<?php
namespace App\Controller;

use App\Entity\AccessToken;
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
class DiscordOAuthController extends Controller
{
    /**
     * @Route("/oauth2", name="oauth2")
     *
     * @param Request $request
     * @param Discord $provider
     *
     * @return Response
     */
    public function indexAction(Request $request, Discord $provider)
    {
        $url = $provider->getAuthorizationUrl();
        $request->getSession()->set('oauth2state', $url);

        return new RedirectResponse($url);
    }

    /**
     * @Route("/oauth2/logout", name="oauth2_logout")
     * @param Request               $request
     * @param TokenStorageInterface $tokenStorage
     *
     * @return RedirectResponse
     */
    public function logoutAction(Request $request, TokenStorageInterface $tokenStorage)
    {
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

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
        if (!$session->get('oauth2state')) {
            throw $this->createAccessDeniedException();
        }
        $session->remove('oauth2state');

        $token         = $provider->getAccessToken(
            'authorization_code',
            [
                'code' => $request->query->get('code')
            ]
        );
        $resourceOwner = $provider->getResourceOwner($token)->toArray();

        $em       = $this->getDoctrine()->getManager();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user     = $userRepo->findByDiscordID($resourceOwner['id']);
        if (!$user) {
            /** @var User $user */
            $user = $userManager->createUser();
            $user
                ->setEnabled(true)
                ->setPassword('')
                ->setEmail($resourceOwner['email'])
                ->setUsername($resourceOwner['username'])
                ->setDiscordID($resourceOwner['id'])
                ->setDiscordUsername($resourceOwner['username'])
                ->setDiscordEmail($resourceOwner['email'])
                ->setDiscordAvatar($resourceOwner['avatar'])
                ->setDiscordDiscriminator($resourceOwner['discriminator'])
                ->setDiscordAvatar($resourceOwner['avatar']);
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

        return new RedirectResponse('/');
    }
}
