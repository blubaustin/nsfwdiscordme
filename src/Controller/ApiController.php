<?php
namespace App\Controller;

use App\Component\NonceComponentInterface;
use App\Entity\BumpPeriod;
use App\Entity\BumpPeriodVote;
use App\Entity\Server;
use App\Entity\ServerTeamMember;
use App\Event\BumpEvent;
use App\Event\JoinEvent;
use App\Event\ServerActionEvent;
use App\Http\Request;
use App\Media\WebHandlerInterface;
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
 * @Route("/api/v1", name="api_", options={"expose"=true}, requirements={"serverID"="\d+"})
 */
class ApiController extends Controller
{
    const NONCE_RECAPTCHA = 'recaptcha';

    /**
     * @var NonceComponentInterface
     */
    protected $nonce;

    /**
     * @param NonceComponentInterface $nonce
     *
     * @return $this
     */
    public function setNonceComponent(NonceComponentInterface $nonce)
    {
        $this->nonce = $nonce;

        return $this;
    }

    /**
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
     * @Route("/guilds", name="guilds")
     *
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function meGuildsAction()
    {
        $resp = $this->discord->fetchMeGuilds($this->getUser()->getDiscordAccessToken());

        return new JsonResponse($resp);
    }

    /**
     * @Route("/guild/{serverID}", name="guild")
     *
     * @param string $serverID
     *
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function guildAction($serverID)
    {
        $resp = $this->discord->fetchGuild($serverID);

        return new JsonResponse($resp);
    }

    /**
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
     * @Route("/bump/period", name="bump_period")
     *
     * @throws Exception
     */
    public function bumpPeriod()
    {
        $bumpPeriod = $this->em->getRepository(BumpPeriod::class)->findCurrentPeriod();

        return new JsonResponse([
            'message' => 'ok',
            'period'  => $bumpPeriod->getFormattedDate()
        ]);
    }

    /**
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
        if ($this->nonce->get(self::NONCE_RECAPTCHA) !== 'bump-ready') {
            throw $this->createAccessDeniedException();
        }
        $this->nonce->remove(self::NONCE_RECAPTCHA);

        $bumped = [];
        $user   = $this->getUser();
        $repo   = $this->em->getRepository(Server::class);
        foreach($request->request->get('servers') as $serverID) {
            $server = $repo->findByDiscordID($serverID);
            if ($server
                && $this->hasServerAccess($server, self::SERVER_ROLE_EDITOR, $user)
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
        if ($this->nonce->get(self::NONCE_RECAPTCHA) != $serverID) {
            throw $this->createAccessDeniedException();
        }
        $this->nonce->remove(self::NONCE_RECAPTCHA);

        $server = $this->em->getRepository(Server::class)->findByDiscordID($serverID);
        if (!$server) {
            throw $this->createNotFoundException();
        }
        if (!$this->hasServerAccess($server, self::SERVER_ROLE_EDITOR)) {
            throw $this->createAccessDeniedException();
        }
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
     * @Route("/bump/{serverID}/me", name="bump_me")
     *
     * @param int $serverID
     *
     * @return JsonResponse
     * @throws NonUniqueResultException
     * @throws DBALException
     */
    public function bumpMeAction($serverID)
    {
        $server = $this->em->getRepository(Server::class)->findByDiscordID($serverID);
        if (!$server) {
            throw $this->createNotFoundException();
        }
        if (!$this->hasServerAccess($server, self::SERVER_ROLE_EDITOR)) {
            throw $this->createAccessDeniedException();
        }

        return new JsonResponse([
            'message' => 'ok',
            'voted'   => $this->hasVotedCurrentBumpPeriod($server)
        ]);
    }

    /**
     * @Route("/bump/ready", name="bump_ready")
     *
     * @return JsonResponse
     * @throws DBALException
     * @throws NonUniqueResultException
     */
    public function bumpReadyAction()
    {
        $ready = [];
        foreach($this->getUser()->getServers() as $server) {
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
            $this->nonce->set(self::NONCE_RECAPTCHA, $nonce);

            return new JsonResponse([
                'success' => true
            ]);
        } else {
            $this->nonce->remove(self::NONCE_RECAPTCHA);
        }

        return new JsonResponse([
            'success' => false
        ]);
    }

    /**
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
        $server   = $this->em->getRepository(Server::class)->findByDiscordID($serverID);
        if (!$server) {
            throw $this->createNotFoundException();
        }

        if ($server->isBotHumanCheck() && $this->nonce->get(self::NONCE_RECAPTCHA) !== "join-${serverID}") {
            return new JsonResponse([
                'message' => 'recaptcha'
            ]);
        }
        if ($server->getServerPassword() && !password_verify($password, $server->getServerPassword())) {
            return new JsonResponse([
                'message' => 'password'
            ]);
        }

        $this->nonce->remove(self::NONCE_RECAPTCHA);

        $inviteChannel = $server->getBotInviteChannelID();
        if ($inviteChannel) {
            $invite = $this->discord->createInvite($inviteChannel);
            if (!$invite || !isset($invite['code'])) {
                return new JsonResponse([
                    'message' => 'error'
                ], 500);
            }
            $inviteURL = "https://discordapp.com/invite/${invite['code']}";
        } else {
            $widget = $this->discord->fetchWidget($server->getDiscordID());
            if (!$widget || !isset($widget['instant_invite'])) {
                return new JsonResponse([
                    'message' => 'error'
                ], 500);
            }
            $inviteURL = $widget['instant_invite'];
        }

        $this->eventDispatcher->dispatch('app.server.join', new JoinEvent($server, $request));

        return new JsonResponse([
            'message'  => 'ok',
            'redirect' => $inviteURL
        ]);
    }

    /**
     * @Route("/delete-server/{serverID}", name="delete_server", methods={"POST"})
     *
     * @param string              $serverID
     * @param WebHandlerInterface $webHandler
     *
     * @return JsonResponse
     */
    public function deleteServerAction($serverID, WebHandlerInterface $webHandler)
    {
        $server = $this->em->getRepository(Server::class)->findByDiscordID($serverID);
        if (!$server || !$this->hasServerAccess($server, self::SERVER_ROLE_MANAGER)) {
            throw $this->createAccessDeniedException();
        }

        $iconMedia   = $server->getIconMedia();
        $bannerMedia = $server->getBannerMedia();
        $teamMembers = $this->em->getRepository(ServerTeamMember::class)->findByServer($server);
        foreach($teamMembers as $teamMember) {
            $this->em->remove($teamMember);
        }

        $this->em->remove($server);
        $this->em->flush();

        try {
            if ($iconMedia) {
                $webHandler->getAdapter()->remove($iconMedia->getPath());
                $this->em->remove($iconMedia);
            }
            if ($bannerMedia) {
                $webHandler->getAdapter()->remove($bannerMedia->getPath());
                $this->em->remove($bannerMedia);
            }
            $this->em->flush();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['serverID' => $serverID]);
        }

        return new JsonResponse([
            'message' => 'ok'
        ]);
    }

    /**
     * @Route("/stats/joins/{serverID}", name="stats_joins")
     *
     * @param string $serverID
     *
     * @return JsonResponse
     * @throws DBALException
     */
    public function statsJoinsAction($serverID)
    {
        $server = $this->em->getRepository(Server::class)->findByDiscordID($serverID);
        if (!$server || !$this->hasServerAccess($server, self::SERVER_ROLE_EDITOR)) {
            throw $this->createAccessDeniedException();
        }

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
}
