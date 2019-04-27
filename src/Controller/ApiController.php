<?php
namespace App\Controller;

use App\Entity\BumpPeriod;
use App\Entity\BumpPeriodVote;
use App\Entity\Server;
use App\Event\BumpEvent;
use App\Event\JoinEvent;
use App\Event\ServerActionEvent;
use App\Http\Request;
use App\Media\WebHandlerInterface;
use App\Security\NonceStorageInterface;
use App\Services\RecaptchaService;
use DateInterval;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
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
     * Returns a list of channels for the given server
     *
     * @Route("/guilds/{serverID}/channels", name="guild_channels")
     *
     * @param string $serverID
     *
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function guildChannelsAction($serverID)
    {
        return new JsonResponse([
            'message'  => 'ok',
            'channels' => $this->discord->fetchGuildChannels($serverID)
        ]);
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
     * @throws DBALException
     * @throws NonUniqueResultException
     */
    public function bumpMultiAction(Request $request)
    {
        $this->validNonceOrThrow(self::NONCE_RECAPTCHA, 'bump-ready');

        $bumped = [];
        $repo   = $this->em->getRepository(Server::class);
        foreach($request->request->get('servers') as $serverID) {
            $server = $repo->findByDiscordID($serverID);
            if ($server
                && $this->hasServerAccess($server, self::SERVER_ROLE_EDITOR)
                && !$this->hasVotedCurrentBumpPeriod($server)) {

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
     * @throws NonUniqueResultException
     * @throws DBALException
     */
    public function bumpAction(Request $request, $serverID)
    {
        $this->validNonceOrThrow(self::NONCE_RECAPTCHA, $serverID);

        $server = $this->findServerOrThrow($serverID, self::SERVER_ROLE_EDITOR);
        if ($this->hasVotedCurrentBumpPeriod($server)) {
            return new JsonResponse([
                'message' => 'Already voted for this bump period.'
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
     * @throws NonUniqueResultException
     * @throws DBALException
     */
    public function bumpServerReadyAction($serverID)
    {
        $server = $this->findServerOrThrow($serverID, self::SERVER_ROLE_EDITOR);

        return new JsonResponse([
            'message' => 'ok',
            'voted'   => $this->hasVotedCurrentBumpPeriod($server)
        ]);
    }

    /**
     * Returns a list of servers for which the authenticated user is a team member which are ready to bump
     *
     * @Route("/bump/ready", name="bump_ready")
     *
     * @return JsonResponse
     * @throws DBALException
     * @throws NonUniqueResultException
     */
    public function bumpReadyAction()
    {
        $servers = $this->em->getRepository(Server::class)
            ->findByTeamMemberUser($this->getUser());

        $ready = [];
        foreach($servers as $server) {
            if ($this->hasServerAccess($server, self::SERVER_ROLE_EDITOR)
                && !$this->hasVotedCurrentBumpPeriod($server)) {
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
     * @Route("/recaptcha-verify", name="recaptcha_verify", methods={"POST"})
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

        $this->eventDispatcher->dispatch('app.server.join', new JoinEvent($server, $request));

        return new JsonResponse([
            'message'  => 'ok',
            'redirect' => $redirect
        ]);
    }

    /**
     * Deletes a server
     *
     * @Route("/delete-server/{serverID}", name="delete_server", methods={"POST"})
     *
     * @param string              $serverID
     * @param WebHandlerInterface $webHandler
     *
     * @return JsonResponse
     */
    public function deleteServerAction($serverID, WebHandlerInterface $webHandler)
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
     * Returns the views & joins stats for a server
     *
     * @Route("/stats/joins/{serverID}", name="stats_joins")
     *
     * @param string $serverID
     *
     * @return JsonResponse
     * @throws DBALException
     */
    public function statsJoinsAction($serverID)
    {
        $server = $this->findServerOrThrow($serverID, self::SERVER_ROLE_EDITOR);

        $stmtJoin = $this->em->getConnection()->prepare('
            SELECT COUNT(*) as `count`
            FROM `server_join_event`
            WHERE `server_id` = ?
            AND DATE(date_created) = ?
            LIMIT 1
        ');
        $stmtView = $this->em->getConnection()->prepare('
            SELECT COUNT(*) as `count`
            FROM `server_view_event`
            WHERE `server_id` = ?
            AND DATE(date_created) = ?
            LIMIT 1
        ');

        $joins = [];
        $views = [];
        $sid   = $server->getId();
        $now   = new DateTime('30 days ago');
        $int   = new DateInterval('P1D');

        for($i = 30; $i > 0; $i--) {
            $day = $now->add($int)->format('Y-m-d');
            $stmtJoin->execute([$sid, $day]);
            $stmtView->execute([$sid, $day]);

            $joins[] = [
                'day'   => $day,
                'count' => $stmtJoin->fetchColumn(0)
            ];
            $views[] = [
                'day'   => $day,
                'count' => $stmtView->fetchColumn(0)
            ];
        }

        return new JsonResponse([
            'message' => 'ok',
            'joins'   => $joins,
            'views'   => $views
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
     * @throws DBALException
     * @throws NonUniqueResultException
     */
    private function bumpServer(Server $server, Request $request)
    {
        $user       = $this->getUser();
        $bumpPeriod = $this->em->getRepository(BumpPeriod::class)->findCurrentPeriod();
        $bumpPeriodVote = (new BumpPeriodVote())
            ->setUser($user)
            ->setBumpPeriod($bumpPeriod)
            ->setServer($server);

        switch($server->getPremiumStatus()) {
            case Server::STATUS_GOLD:
                $points = 2;
                break;
            case Server::STATUS_PLATINUM:
            case Server::STATUS_MASTER:
                $points = 3;
                break;
            default:
                $points = 1;
                break;
        }

        $server->incrementBumpPoints($points);
        $this->em->persist($bumpPeriodVote);
        $this->em->flush();

        $this->eventDispatcher->dispatch('app.server.bump', new BumpEvent($server, $request));
        $this->eventDispatcher->dispatch(
            'app.server.action',
            new ServerActionEvent($server, $user, 'Bumped server.')
        );

        return [
            'bumpPoints' => $server->getBumpPoints(),
            'bumpUser'   => $user->getDiscordUsername() . '#' . $user->getDiscordDiscriminator(),
            'bumpDate'   => $bumpPeriodVote->getDateCreated()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * @param Server $server
     *
     * @return bool
     * @throws NonUniqueResultException
     * @throws DBALException
     */
    private function hasVotedCurrentBumpPeriod(Server $server)
    {
        $bumpPeriod = $this->em->getRepository(BumpPeriod::class)->findCurrentPeriod();
        $vote       = $this->em->getRepository(BumpPeriodVote::class)
            ->findOneBy([
                'bumpPeriod' => $bumpPeriod,
                'server'     => $server
            ]);

        return (bool)$vote;
    }

    /**
     * @param string $key
     * @param string $value
     */
    private function validNonceOrThrow($key, $value)
    {
        if (!$this->nonceStorage->valid($key, $value)) {
            throw $this->createAccessDeniedException();
        }
    }
}
