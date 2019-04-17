<?php
namespace App\Twig;

use App\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class UserExtension
 */
class UserExtension extends AbstractExtension
{
    const DISCORD_CDN_URL = 'https://cdn.discordapp.com';

    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('avatar', [$this, 'avatar'])
        ];
    }

    /**
     * @param User   $user
     * @param string $ext
     *
     * @return string
     */
    public function avatar(User $user, $ext = 'png')
    {
        $avatarHash = $user->getDiscordAvatar();
        $discordID  = $user->getDiscordID();
        if ($avatarHash && $discordID) {
            return sprintf('%s/avatars/%d/%s.%s', self::DISCORD_CDN_URL, $discordID, $avatarHash, $ext);
        }

        return '';
    }
}
