<?php
namespace App\Media;

use App\Entity\Media;
use App\Media\Adapter\AdapterInterface;

/**
 * Class WebHandler
 */
class WebHandler implements WebHandlerInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $cdnRootURLs = [];

    /**
     * Constructor
     *
     * @param AdapterInterface $adapter
     * @param array            $cdnRootURLs
     */
    public function __construct(AdapterInterface $adapter, array $cdnRootURLs)
    {
        $this->adapter     = $adapter;
        $this->cdnRootURLs = $cdnRootURLs;
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
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCDNRootURLs()
    {
        return $this->cdnRootURLs;
    }

    /**
     * {@inheritDoc}
     */
    public function setCDNRootURLs(array $cdnRootURLs)
    {
        $this->cdnRootURLs = $cdnRootURLs;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function write($name, $path, $localFile)
    {
        $this->adapter->write($path, $localFile, [
            'mkdir' => true
        ]);
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
        if (!isset($this->cdnRootURLs[$adapter])) {
            throw new Exception\AdapterNotFoundException(
                "CDN not found for adapter ${adapter}."
            );
        }

        return sprintf('%s/%s', $this->cdnRootURLs[$adapter], $media->getPath());
    }
}
