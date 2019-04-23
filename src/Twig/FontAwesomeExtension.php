<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class FontAwesomeExtension
 */
class FontAwesomeExtension extends AbstractExtension
{
    /**
     * Icon names which are aliases to real icon names. An optional
     * css class can be specified by separating the real icon name
     * by the class name, i.e. "gem far".
     */
    const ICON_ALIASES = [
        'app-bump'            => 'fire',
        'app-recently-bumped' => 'burn',
        'app-gem'             => 'gem far',
        'app-stats'           => 'chart-bar',
        'app-upgrade'         => 'arrow-circle-up',
        'app-settings'        => 'cog',
        'app-trending'        => 'chart-line',
        'app-online'          => 'circle',
        'app-most-online'     => 'user',
        'app-random'          => 'random',
        'app-delete'          => 'trash-alt',
        'app-join'            => 'sign-in-alt'
    ];

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('icon', [$this, 'icon'], ['is_safe' => ['html']])
        ];
    }

    /**
     * Returns the html for a Font Awesome icon
     *
     * @param string $id      The name of the icon
     * @param string $classes Additional classes applied to the tag, space separate, see
     * @param string $title   Value for the tag title attribute
     *
     * @return string
     */
    public function icon($id, $classes = "fa", $title = "")
    {
        if ($title) {
            $title = htmlspecialchars($title, ENT_HTML5 | ENT_QUOTES);
            $title = " title=\"{$title}\"";
        }

        if (isset(self::ICON_ALIASES[$id])) {
            list($id, $c) = explode(' ', self::ICON_ALIASES[$id] . ' ');
            if ($c) {
                $classes = $c;
            }
        }

        return sprintf('<span class="icon %s fa-%s"%s></span>', $classes, $id, $title);
    }
}
