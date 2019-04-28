<?php
namespace App\Controller;

use App\Entity\BannedUser;
use App\Entity\ServerTeamMember;
use App\Security\UserProvider;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * @Route()
 */
class AuthController extends Controller
{
    const OAUTH2_STATE_KEY = 'oauth2state';
    const OAUTH2_BACK_KEY  = 'oauth2back';

    /**
     * @Route("/login", name="login")
     *
     * @param Request      $request
     * @param UserProvider $provider
     *
     * @return Response
     */
    public function loginAction(Request $request, UserProvider $provider)
    {
        // The saved state key will be validated in discordOauth2RedirectAction().

        $url     = $provider->getAuthorizationURL();
        $session = $request->getSession();
        $session->set(self::OAUTH2_STATE_KEY, $url);
        if ($back = $request->query->get('back')) {
            $session->set(self::OAUTH2_BACK_KEY, $back);
        }

        return new RedirectResponse($url);
    }

    /**
     * @Route("/logout", name="logout")
     * @param Request               $request
     * @param TokenStorageInterface $tokenStorage
     *
     * @return RedirectResponse
     */
    public function logoutAction(Request $request, TokenStorageInterface $tokenStorage)
    {
        $tokenStorage->setToken(null);
        $session = $request->getSession();
        $session->invalidate();
        $session->remove(self::OAUTH2_STATE_KEY);

        return new RedirectResponse('/');
    }

    /**
     * @Route("/discord/oauth2/redirect", name="discord_oauth2_redirect")
     *
     * @param Request               $request
     * @param UserProvider          $provider
     * @param TokenStorageInterface $tokenStorage
     *
     * @return RedirectResponse
     */
    public function discordOauth2RedirectAction(
        Request $request,
        UserProvider $provider,
        TokenStorageInterface $tokenStorage
    ) {
        // We saved this session value in loginAction(). Ensures
        // the user arrived at this route via the login path.
        $session = $request->getSession();
        if (!$session->get(self::OAUTH2_STATE_KEY)) {
            throw $this->createAccessDeniedException();
        }
        $session->remove(self::OAUTH2_STATE_KEY);

        try {
            $code = $request->query->get('code');
            if (!$code) {
                throw new Exception('No access code provided.');
            }

            $user = $provider->createUser($code);
            if (!$user) {
                throw new Exception('User not created.');
            }
        } catch (Exception $e) {
            $this->addFlash('danger', 'Unable to authenticate your account.');

            return $this->logoutAction($request, $tokenStorage);
        }

        // Make sure this motha fucka is allowed on the site.
        $isBanned = $this->em->getRepository(BannedUser::class)->isBanned(
            $user->getDiscordUsername(),
            $user->getDiscordDiscriminator()
        );
        if (!$user->isEnabled() || $isBanned) {
            $this->addFlash('danger', 'Your account has been banned.');

            return $this->logoutAction($request, $tokenStorage);
        }

        // Team members may be created by username#discriminator without them having ever
        // visited this site. Associate the authenticated user with the team member now.
        $teamMember = $this->em->getRepository(ServerTeamMember::class)
            ->findByDiscordUsernameAndDiscriminator(
                $user->getDiscordUsername(),
                $user->getDiscordDiscriminator()
            );
        if ($teamMember) {
            if (!$teamMember->getDiscordID()) {
                $teamMember->setDiscordID($user->getDiscordID());
            }
            if (!$teamMember->getDiscordAvatar()) {
                $teamMember->setDiscordAvatar($user->getDiscordAvatar());
            }
            if (!$teamMember->getUser()) {
                $teamMember->setUser($user);
            }
            $this->em->flush();
        }

        // Authenticate with Symfony.
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $tokenStorage->setToken($token);
        $request->getSession()->set('_security_main', serialize($token));
        $this->eventDispatcher->dispatch(
            'security.interactive_login',
            new InteractiveLoginEvent($request, $token)
        );

        // We're done! Send the user back where they came from or else the home page.
        $session = $this->get('session');
        if ($back = $session->get(self::OAUTH2_BACK_KEY)) {
            return new RedirectResponse($back);
        }

        return new RedirectResponse(
            $this->generateUrl('home_index')
        );
    }
}
