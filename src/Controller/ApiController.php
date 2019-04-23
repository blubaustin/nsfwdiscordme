<?php
namespace App\Controller;

use App\Entity\BumpPeriod;
use App\Entity\BumpPeriodVote;
use App\Entity\Server;
use App\Event\BumpEvent;
use App\Event\JoinEvent;
use App\Http\Request;
use App\Services\RecaptchaService;
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
        $server = $this->em->getRepository(Server::class)->findByDiscordID($serverID);
        if (!$server) {
            throw $this->createNotFoundException();
        }
        if ($server->getUser()->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        // Was saved in recaptchaVerifyAction() when verifying the recaptcha.
        if ($request->getSession()->get('recaptcha_nonce') != $serverID) {
            throw $this->createAccessDeniedException();
        }
        $request->getSession()->remove('recaptcha_nonce');

        if ($this->hasVotedCurrentBumpPeriod($server)) {
            return new JsonResponse([
                'message' => 'Already voted for this bump period.'
            ], 403);
        }

        $bumpPeriod = $this->em->getRepository(BumpPeriod::class)->findCurrentPeriod();
        $bumpPeriodVote = (new BumpPeriodVote())
            ->setUser($this->getUser())
            ->setBumpPeriod($bumpPeriod)
            ->setServer($server);
        $server->incrementBumpPoints();
        $this->em->persist($bumpPeriodVote);
        $this->em->flush();

        $this->eventDispatcher->dispatch('app.bump', new BumpEvent($server, $request));

        return new JsonResponse([
            'message'    => 'ok',
            'bumpPoints' => $server->getBumpPoints()
        ]);
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
        if ($server->getUser()->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        return new JsonResponse([
            'message' => 'ok',
            'voted'   => $this->hasVotedCurrentBumpPeriod($server)
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
        $session = $request->getSession();
        $nonce   = $request->request->get('nonce');
        $token   = $request->request->get('token');
        if (!$nonce || !$token) {
            throw $this->createNotFoundException();
        }

        if ($recaptchaService->verify($token)) {
            $session->set('recaptcha_nonce', $nonce);

            return new JsonResponse([
                'success' => true
            ]);
        } else {
            $session->remove('recaptcha_nonce');
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
        $session  = $request->getSession();
        $password = trim($request->request->get('password'));
        $server   = $this->em->getRepository(Server::class)->findByDiscordID($serverID);
        if (!$server) {
            throw $this->createNotFoundException();
        }

        if ($server->isBotHumanCheck() && $session->get('recaptcha_nonce') !== "join-${serverID}") {
            return new JsonResponse([
                'message' => 'recaptcha'
            ]);
        }
        if ($server->getServerPassword() && !password_verify($password, $server->getServerPassword())) {
            return new JsonResponse([
                'message' => 'password'
            ]);
        }

        $session->remove('recaptcha_nonce');

        $invite = $this->discord->createInvite($server->getBotInviteChannelID());
        if (!$invite || !isset($invite['code'])) {
            return new JsonResponse([
                'message' => 'error'
            ], 500);
        }

        $this->eventDispatcher->dispatch('app.join', new JoinEvent($server, $request));

        return new JsonResponse([
            'message'  => 'ok',
            'redirect' => "https://discordapp.com/invite/${invite['code']}"
        ]);
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
