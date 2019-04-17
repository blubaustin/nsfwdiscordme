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

        return sprintf('<span class="icon %s fa-%s"%s></span>', $classes, $id, $title);
    }
}
