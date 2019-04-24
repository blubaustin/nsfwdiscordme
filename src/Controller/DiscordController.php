<?php
namespace App\Controller;

use App\Entity\AccessToken;
use App\Entity\BannedUser;
use App\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Wohali\OAuth2\Client\Provider\Discord;
use DateTime;

/**
 * @Route("/discord", name="discord_")
 */
class DiscordController extends Controller
{
    const OAUTH2_STATE_KEY = 'oauth2state';
    const OAUTH2_BACK_KEY  = 'oauth2back';

    /**
     * @Route("/oauth2", name="oauth2")
     *
     * @param Request $request
     * @param Discord $provider
     *
     * @return Response
     */
    public function oauth2Action(Request $request, Discord $provider)
    {
        $url = $provider->getAuthorizationUrl();

        $session = $request->getSession();
        $session->set(self::OAUTH2_STATE_KEY, $url);
        if ($back = $request->query->get('back')) {
            $session->set(self::OAUTH2_BACK_KEY, $back);
        }

        return new RedirectResponse($url);
    }

    /**
     * @Route("/oauth2/logout", name="oauth2_logout")
     * @param Request               $request
     * @param TokenStorageInterface $tokenStorage
     *
     * @return RedirectResponse
     */
    public function oauth2LogoutAction(Request $request, TokenStorageInterface $tokenStorage)
    {
        $tokenStorage->setToken(null);
        $session = $request->getSession();
        $session->invalidate();
        $session->remove(self::OAUTH2_STATE_KEY);

        return new RedirectResponse('/');
    }

    /**
     * @Route("/oauth2/redirect", name="oauth2_redirect")
     *
     * @param Request               $request
     * @param Discord               $provider
     * @param UserManagerInterface  $userManager
     * @param TokenStorageInterface $tokenStorage
     *
     * @return RedirectResponse
     * @throws IdentityProviderException
     */
    public function oauth2RedirectAction(
        Request $request,
        Discord $provider,
        UserManagerInterface $userManager,
        TokenStorageInterface $tokenStorage
    ) {
        $session = $request->getSession();
        if (!$session->get(self::OAUTH2_STATE_KEY)) {
            throw $this->createAccessDeniedException();
        }
        $session->remove(self::OAUTH2_STATE_KEY);

        $code = $request->query->get('code');
        if (!$code) {
            return $this->getRedirectResponse();
        }

        $token = $provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
        if (!$token) {
            return $this->getRedirectResponse();
        }
        $owner = $provider->getResourceOwner($token);
        if (!$owner) {
            return $this->getRedirectResponse();
        }
        $owner = $owner->toArray();

        $bannedUserRepo = $this->em->getRepository(BannedUser::class);
        if ($bannedUserRepo->isBanned($owner['username'], $owner['discriminator'])) {
            $this->addFlash('danger', 'You are banned from the site.');

            return $this->getRedirectResponse();
        }

        $em       = $this->getDoctrine()->getManager();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user     = $userRepo->findByDiscordID($owner['id']);
        if (!$user) {
            /** @var User $user */
            $user = $userManager->createUser();
            $user
                ->setEnabled(true)
                ->setPassword('')
                ->setEmail('')
                ->setUsername('')
                ->setDiscordID($owner['id'])
                ->setDiscordUsername($owner['username'])
                ->setDiscordEmail($owner['email'])
                ->setDiscordAvatar($owner['avatar'])
                ->setDiscordDiscriminator($owner['discriminator'])
                ->setDiscordAvatar($owner['avatar']);
            $userManager->updateUser($user);
            $accessToken = new AccessToken();
        } else {
            $accessToken = $user->getDiscordAccessToken();
            if (!$accessToken) {
                $accessToken = new AccessToken();
            }
        }

        $values      = $token->getValues();
        $dateExpires = (new DateTime())->setTimestamp($token->getExpires());
        $accessToken
            ->setUser($user)
            ->setToken($token->getToken())
            ->setRefreshToken($token->getRefreshToken())
            ->setDateExpires($dateExpires)
            ->setScope($values['scope'])
            ->setType($values['token_type']);
        $em->persist($accessToken);
        $em->flush();

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $tokenStorage->setToken($token);
        $request->getSession()->set('_security_main', serialize($token));
        $this->eventDispatcher->dispatch(
            'security.interactive_login',
            new InteractiveLoginEvent($request, $token)
        );

        return $this->getRedirectResponse();
    }

    /**
     * @return RedirectResponse
     */
    private function getRedirectResponse()
    {
        $session = $this->get('session');
        if ($back = $session->get(self::OAUTH2_BACK_KEY)) {
            return new RedirectResponse($back);
        }

        return new RedirectResponse(
            $this->generateUrl('home_index')
        );
    }
}
