<?php
namespace App\Controller;

use App\Entity\Server;
use App\Entity\ServerEvent;
use App\Event\AppEvents;
use App\Event\BumpEvent;
use App\Event\JoinEvent;
use App\Event\ServerActionEvent;
use App\Http\Request;
use App\Media\WebHandlerInterface;
use App\Security\NonceStorageInterface;
use App\Services\Exception\DiscordRateLimitException;
use App\Services\RecaptchaService;
use DateInterval;
use DateTime;
use Elastica\Aggregation\DateHistogram;
use Elastica\Query;
use Exception;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * These routes are all called from javascript.
 *
 * The script bin/routes.sh must be run when making any changes/additions to the routes
 * in this controller. The script generates the routes.json needed by javascript.
 *
 * @Route("/api/v1", name="api_", options={"expose"=true}, requirements={"serverID"="\d+"})
 */
class ApiController extends Controller
{
    const NONCE_RECAPTCHA = 'recaptcha';

    /**
     * @var NonceStorageInterface
     */
    protected $nonceStorage;

    /**
     * @var PaginatedFinderInterface
     */
    protected $eventsFinder;

    /**
     * @param NonceStorageInterface $nonceStorage
     *
     * @return $this
     */
    public function setNonceStorage(NonceStorageInterface $nonceStorage)
    {
        $this->nonceStorage = $nonceStorage;

        return $this;
    }

    /**
     * @param PaginatedFinderInterface $eventsFinder
     *
     * @return $this
     */
    public function setEventsFinder(PaginatedFinderInterface $eventsFinder)
    {
        $this->eventsFinder = $eventsFinder;

        return $this;
    }

    /**
     * Returns the widget for the given server
     *
     * @Route("/widget/{serverID}", name="widget")
     *
     * @param string $serverID
     *
     * @return Response
     * @throws GuzzleException
     */
    public function widgetAction($serverID)
    {
        try {
            $resp = $this->discord->fetchWidget($serverID);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse($resp);
    }

    /**
     * Bumps multiple servers
     *
     * The POST data contains an array of server IDs to bump. Returns information
     * on which servers were bumped.
     *
     * @Route("/bump/multi", name="bump_multi", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function bumpMultiAction(Request $request)
    {
        $this->validateNonceOrThrow(self::NONCE_RECAPTCHA, 'bump-ready');

        $bumped = [];
        $repo   = $this->em->getRepository(Server::class);
        foreach($request->request->get('servers') as $serverID) {
            $server = $repo->findByDiscordID($serverID);
            if ($server && $server->isBumpReady() && $this->hasServerAccess($server, self::SERVER_ROLE_EDITOR)) {
                $bumped[$serverID] = $this->bumpServer($server, $request);
            }
        }

        return new JsonResponse([
            'message' => 'ok',
            'bumped'  => $bumped
        ]);
    }

    /**
     * Bumps a single server and returns information on the bump
     *
     * @Route("/bump/{serverID}", name="bump", methods={"POST"})
     *
     * @param Request $request
     * @param int     $serverID
     *
     * @return JsonResponse
     */
    public function bumpAction(Request $request, $serverID)
    {
        $this->validateNonceOrThrow(self::NONCE_RECAPTCHA, $serverID);

        $server = $this->findServerOrThrow($serverID, self::SERVER_ROLE_EDITOR);
        if (!$server->isBumpReady()) {
            return new JsonResponse([
                'message' => 'Server not bump ready.'
            ], 403);
        }

        $result = array_merge([
            'message' => 'ok'
        ], $this->bumpServer($server, $request));

        return new JsonResponse($result);
    }

    /**
     * Returns whether the server is ready for a bump
     *
     * @Route("/bump/ready/{serverID}", name="bump_server_ready")
     *
     * @param string $serverID
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function bumpServerReadyAction($serverID)
    {
        $server = $this->findServerOrThrow($serverID, self::SERVER_ROLE_EDITOR);

        return new JsonResponse([
            'message' => 'ok',
            'ready'   => $server->isBumpReady()
        ]);
    }

    /**
     * Returns a list of servers for which the authenticated user is a team member which are ready to bump
     *
     * @Route("/bump/ready", name="bump_ready")
     *
     * @return JsonResponse
     */
    public function bumpReadyAction()
    {
        $servers = $this->em->getRepository(Server::class)
            ->findByTeamMemberUser($this->getUser());

        $ready = [];
        foreach($servers as $server) {
            if ($this->hasServerAccess($server, self::SERVER_ROLE_EDITOR) && $server->isBumpReady()) {
                $ready[] = $server->getDiscordID();
            }
        }

        return new JsonResponse([
            'message' => 'ok',
            'ready'   => $ready
        ]);
    }

    /**
     * Verifies a recaptcha token with google
     *
     * @Route("/recaptcha/verify", name="recaptcha_verify", methods={"POST"})
     *
     * @param Request          $request
     * @param RecaptchaService $recaptchaService
     *
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function recaptchaVerifyAction(Request $request, RecaptchaService $recaptchaService)
    {
        $nonce = $request->request->get('nonce');
        $token = $request->request->get('token');
        if (!$nonce || !$token) {
            throw $this->createNotFoundException();
        }

        if ($recaptchaService->verify($token)) {
            $this->nonceStorage->set(self::NONCE_RECAPTCHA, $nonce);

            return new JsonResponse([
                'success' => true
            ]);
        } else {
            $this->nonceStorage->remove(self::NONCE_RECAPTCHA);
        }

        return new JsonResponse([
            'success' => false
        ]);
    }

    /**
     * Joins a server
     *
     * @Route("/join/{serverID}", name="join", methods={"POST"})
     *
     * @param string  $serverID
     * @param Request $request
     *
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function joinAction($serverID, Request $request)
    {
        $password = trim($request->request->get('password'));
        $server   = $this->findServerOrThrow($serverID);

        // Ensures the user did the recaptcha if it's required.
        if ($server->isBotHumanCheck() && !$this->nonceStorage->valid(self::NONCE_RECAPTCHA, "join-${serverID}")) {
            return new JsonResponse([
                'message' => 'recaptcha'
            ]);
        }

        // Ensures the password is correct if passwords are enabled.
        if ($server->getServerPassword() && !password_verify($password, $server->getServerPassword())) {
            return new JsonResponse([
                'message' => 'password'
            ]);
        }

        try {
            if ($server->getInviteType() === Server::INVITE_TYPE_BOT && $inviteChannel = $server->getBotInviteChannelID()) {
                $redirect = $this->discord->createBotInviteURL($inviteChannel);
            } else if ($server->getInviteType() === Server::INVITE_TYPE_WIDGET) {
                $redirect = $this->discord->createWidgetInviteURL($server->getDiscordID());
            } else {
                throw new Exception('Invalid invite type.');
            }
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'error'
            ], 500);
        }

        $this->eventDispatcher->dispatch(AppEvents::SERVER_JOIN, new JoinEvent($server, $request));

        return new JsonResponse([
            'message'  => 'ok',
            'redirect' => $redirect
        ]);
    }

    /**
     * Returns a list of channels for the given server
     *
     * @Route("/server/{serverID}/channels", name="server_channels")
     *
     * @param string $serverID
     *
     * @return JsonResponse
     * @throws GuzzleException
     * @throws DiscordRateLimitException
     */
    public function serverChannelsAction($serverID)
    {
        return new JsonResponse([
            'message'  => 'ok',
            'channels' => $this->discord->fetchGuildChannels($serverID)
        ]);
    }

    /**
     * Returns the views & joins stats for a server
     *
     * @Route("/server/{serverID}/stats", name="server_stats")
     *
     * @param string $serverID
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function serverStatsAction($serverID)
    {
        $server = $this->findServerOrThrow($serverID, self::SERVER_ROLE_EDITOR);

        /** @var FantaPaginatorAdapter $adapter */
        $query   = $this->createServerEventQuery($server, ServerEvent::TYPE_JOIN);
        $results = $this->eventsFinder->findPaginated($query);
        $adapter = $results->getAdapter();
        $buckets = $adapter->getAggregations()['hits']['buckets'];
        $joins   = $this->generateStatsFromBuckets($buckets);

        $query   = $this->createServerEventQuery($server, ServerEvent::TYPE_VIEW);
        $results = $this->eventsFinder->findPaginated($query);
        $adapter = $results->getAdapter();
        $buckets = $adapter->getAggregations()['hits']['buckets'];
        $views   = $this->generateStatsFromBuckets($buckets);

        return new JsonResponse([
            'message' => 'ok',
            'joins'   => $joins,
            'views'   => $views
        ]);
    }

    /**
     * Deletes a server
     *
     * @Route("/server/{serverID}/delete", name="server_delete", methods={"POST"})
     *
     * @param string              $serverID
     * @param WebHandlerInterface $webHandler
     *
     * @return JsonResponse
     */
    public function serverDeleteAction($serverID, WebHandlerInterface $webHandler)
    {
        $server = $this->findServerOrThrow($serverID, self::SERVER_ROLE_MANAGER);
        foreach($server->getTeamMembers() as $teamMember) {
            $this->em->remove($teamMember);
        }
        $this->em->remove($server);

        // Flush now because we don't care if there's a problem later deleting the
        // media. Better to delete the server even if deleting the media fails.
        $this->em->flush();

        try {
            $iconMedia   = $server->getIconMedia();
            $bannerMedia = $server->getBannerMedia();
            if ($iconMedia) {
                $webHandler->getAdapter()->remove($iconMedia->getPath());
                $this->em->remove($iconMedia);
                $this->em->flush();
            }
            if ($bannerMedia) {
                $webHandler->getAdapter()->remove($bannerMedia->getPath());
                $this->em->remove($bannerMedia);
                $this->em->flush();
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['serverID' => $serverID]);
        }

        return new JsonResponse([
            'message' => 'ok'
        ]);
    }

    /**
     * Adds the POSTed message to session flash storage
     *
     * @Route("/flash/{type}", name="flash", methods={"POST"})
     *
     * @param string $type
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function flashAction($type, Request $request)
    {
        $message = $request->request->get('message');
        if (!$message || !in_array($type, ['success', 'danger'])) {
            throw $this->createNotFoundException();
        }

        $this->addFlash($type, $message);

        return new JsonResponse([
            'message' => 'ok'
        ]);
    }

    /**
     * Returns the server with the given ID or throws a not found exception
     *
     * When given a role, throws a access denied exception when the authenticated user
     * does not have that role on the server.
     *
     * @param string $serverID
     * @param string $role
     *
     * @return Server
     */
    private function findServerOrThrow($serverID, $role = '')
    {
        $server = $this->em->getRepository(Server::class)->findByDiscordID($serverID);
        if (!$server) {
            throw $this->createNotFoundException();
        }
        if ($role && !$this->hasServerAccess($server, $role)) {
            throw $this->createAccessDeniedException();
        }

        return $server;
    }

    /**
     * @param Server  $server
     * @param Request $request
     *
     * @return array
     */
    private function bumpServer(Server $server, Request $request)
    {
        try {
            $server->setDateBumped(new DateTime());
            $server->incrementBumpPoints($server->getPointsPerBump());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->em->flush();
        $user = $this->getUser();
        $this->eventDispatcher->dispatch(
            AppEvents::SERVER_BUMP,
            new BumpEvent($server, $user, $request)
        );
        $this->eventDispatcher->dispatch(
            AppEvents::SERVER_ACTION,
            new ServerActionEvent($server, $user, 'Bumped server.')
        );

        return [
            'bumpPoints' => $server->getBumpPoints(),
            'bumpUser'   => $user->getDiscordUsername() . '#' . $user->getDiscordDiscriminator(),
            'nextBump'   => Server::BUMP_PERIOD_SECONDS
        ];
    }

    /**
     * @param string $key
     * @param string $value
     */
    private function validateNonceOrThrow($key, $value)
    {
        if (!$this->nonceStorage->valid($key, $value)) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @param Server $server
     * @param int    $eventType
     *
     * @return Query
     */
    private function createServerEventQuery(Server $server, $eventType)
    {
        /** @var FantaPaginatorAdapter $adapter */
        $query = new Query();
        $query->setSize(0);

        $bool = new Query\BoolQuery();
        $bool->addMust(new Query\Term([
            'eventType' => $eventType
        ]));
        $bool->addMust(new Query\Term([
            'server' => $server->getDiscordID()
        ]));
        $query->setQuery($bool);
        $query->addAggregation(new DateHistogram('hits', 'dateCreated', 'day'));

        return $query;
    }

    /**
     * @param array $buckets
     *
     * @return array
     * @throws Exception
     */
    private function generateStatsFromBuckets(array $buckets)
    {
        $rows = [];
        foreach($buckets as $bucket) {
            $day = (new DateTime($bucket['key_as_string']))->format('Y-m-d');
            $rows[$day] = $bucket['doc_count'];
        }

        $final = [];
        $now   = new DateTime('30 days ago');
        $int   = new DateInterval('P1D');
        for($i = 30; $i > 0; $i--) {
            $day = $now->add($int)->format('Y-m-d');
            if (isset($rows[$day])) {
                $final[] = [
                    'day'   => $day,
                    'count' => $rows[$day]
                ];
            } else {
                $final[] = [
                    'day'   => $day,
                    'count' => 0
                ];
            }
        }

        return $final;
    }
}
