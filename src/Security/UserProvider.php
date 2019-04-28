<?php
namespace App\Security;

use App\Entity\AccessToken;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wohali\OAuth2\Client\Provider\Discord;

/**
 * Connects the Discord oauth with the Symfony security component.
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var Discord
     */
    protected $discord;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * Constructor
     *
     * @param Discord                $discord
     * @param EntityManagerInterface $em
     */
    public function __construct(Discord $discord, EntityManagerInterface $em)
    {
        $this->discord = $discord;
        $this->em      = $em;
    }

    /**
     * @return string
     */
    public function getAuthorizationURL()
    {
        return $this->discord->getAuthorizationUrl();
    }

    /**
     * @param string $authorizationCode
     *
     * @return User
     * @throws Exception
     * @throws IdentityProviderException
     */
    public function createUser($authorizationCode)
    {
        /** @var \League\OAuth2\Client\Token\AccessToken  $token */
        $token = $this->discord->getAccessToken('authorization_code', [
            'code' => $authorizationCode
        ]);
        if (!$token) {
            throw new Exception(
                'Could not get token from Discord.'
            );
        }

        $owner = $this->discord->getResourceOwner($token);
        if (!$owner) {
            throw new Exception(
                'Could not get resource owner from Discord.'
            );
        }

        $owner = $owner->toArray();
        if (empty($owner['id'])) {
            throw new Exception(
                'Invalid resource owner from Discord.'
            );
        }

        $user = $this->em->getRepository(User::class)
            ->findByDiscordID($owner['id']);
        if (!$user) {
            $accessToken = new AccessToken();
            $user        = new User();
            $user
                ->setEnabled(true)
                ->setDateLastLogin(new DateTime())
                ->setDiscordID($owner['id'])
                ->setDiscordUsername($owner['username'])
                ->setDiscordEmail($owner['email'])
                ->setDiscordAvatar($owner['avatar'])
                ->setDiscordDiscriminator($owner['discriminator'])
                ->setDiscordAvatar($owner['avatar']);
            $this->em->persist($user);
        } else {
            $user->setDateLastLogin(new DateTime());
            $accessToken = $user->getDiscordAccessToken();
            if (!$accessToken) {
                $accessToken = new AccessToken();
            }
        }

        $values  = $token->getValues();
        $expires = (new DateTime())->setTimestamp($token->getExpires());
        $accessToken
            ->setUser($user)
            ->setToken($token->getToken())
            ->setRefreshToken($token->getRefreshToken())
            ->setDateExpires($expires)
            ->setScope($values['scope'])
            ->setType($values['token_type']);
        $this->em->persist($accessToken);

        $this->em->flush();

        return $user;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return void
     */
    public function loadUserByUsername($username)
    {
        // This method is never used.
        throw new UsernameNotFoundException();
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface|User $user
     *
     * @return UserInterface
     * @throws IdentityProviderException
     */
    public function refreshUser(UserInterface $user)
    {
        if (!($user instanceof User)) {
            return null;
        }

        $accessToken = $user->getDiscordAccessToken();
        if ($accessToken && !$accessToken->isExpired()) {
            return $user;
        }

        $newAccessToken = $this->discord->getAccessToken('refresh_token', [
            'refresh_token' => $accessToken->getRefreshToken()
        ]);
        if ($newAccessToken) {
            $values      = $newAccessToken->getValues();
            $dateExpires = (new DateTime())->setTimestamp($newAccessToken->getExpires());
            $accessToken
                ->setToken($newAccessToken->getToken())
                ->setRefreshToken($newAccessToken->getRefreshToken())
                ->setDateExpires($dateExpires)
                ->setScope($values['scope'])
                ->setType($values['token_type']);
            $this->em->flush();

            return $user;
        }

        return null;
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return User::class === $class;
    }
}
