<?php
namespace App\Media;

use App\Entity\Media;
use App\Media\Adapter\AdapterInterface;
use App\Repository\MediaRepository;

/**
 * Class MediaHandler
 */
class MediaHandler implements MediaHandlerInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var MediaRepository
     */
    protected $repo;

    /**
     * @var array
     */
    protected $cndRootURLs = [];

    /**
     * Constructor
     *
     * @param MediaRepository  $repo
     * @param AdapterInterface $adapter
     * @param array            $cdnRootURLs
     */
    public function __construct(MediaRepository $repo, AdapterInterface $adapter, array $cdnRootURLs)
    {
        $this->repo        = $repo;
        $this->adapter     = $adapter;
        $this->cndRootURLs = $cdnRootURLs;
    }

    /**
     * {@inheritDoc}
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * {@inheritDoc}
     */
    public function write($name, $path, $localFile)
    {
        $this->adapter->write($path, $localFile);
        $media = new Media();
        $media
            ->setAdapter($this->adapter->getName())
            ->setName($name)
            ->setPath($path);

        return $media;
    }

    /**
     * {@inheritDoc}
     */
    public function getWebURL(Media $media)
    {
        $adapter = $media->getAdapter();
        if (!isset($this->cndRootURLs[$adapter])) {
            throw new Exception\AdapterNotFoundException(
                "CDN not found for adapter ${adapter}."
            );
        }

        return sprintf('%s/%s', $this->cndRootURLs[$adapter], $media->getPath());
    }
}
