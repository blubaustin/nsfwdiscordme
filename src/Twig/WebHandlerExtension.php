<?php
namespace App\Twig;

use App\Entity\Media;
use App\Media\Exception\AdapterNotFoundException;
use App\Media\WebHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * Constructor
     *
     * @param WebHandlerInterface    $webHandler
     * @param EntityManagerInterface $em
     */
    public function __construct(WebHandlerInterface $webHandler, EntityManagerInterface $em)
    {
        $this->webHandler = $webHandler;
        $this->em         = $em;
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
     * @param Media|string $media
     *
     * @return string
     * @throws AdapterNotFoundException
     */
    public function webPath($media)
    {
        if ($media instanceof Media) {
            return $this->webHandler->getWebURL($media);
        } else {
            $media = $this->em->getRepository(Media::class)->findByPath($media);
            return $this->webHandler->getWebURL($media);
        }
    }
}
