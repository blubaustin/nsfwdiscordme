<?php
namespace App\Twig;

use App\Entity\Media;
use App\Media\Exception\AdapterNotFoundException;
use App\Media\WebHandlerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class WebHandlerExtension
 */
class WebHandlerExtension extends AbstractExtension
{
    /**
     * @var WebHandlerInterface
     */
    protected $webHandler;

    /**
     * Constructor
     *
     * @param WebHandlerInterface $webHandler
     */
    public function __construct(WebHandlerInterface $webHandler)
    {
        $this->webHandler = $webHandler;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('webPath', [$this, 'webPath'])
        ];
    }

    /**
     * @param Media $media
     *
     * @return string
     * @throws AdapterNotFoundException
     */
    public function webPath(Media $media)
    {
        return $this->webHandler->getWebURL($media);
    }
}
