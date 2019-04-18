<?php
namespace App\Media;

use App\Entity\Media;
use App\Media\Adapter\AdapterInterface;

/**
 * Interface for media handlers
 */
interface MediaHandlerInterface
{
    /**
     * Returns the media adapter being used
     *
     * @return AdapterInterface
     */
    public function getAdapter();

    /**
     * Writes the local file and returns a Media entity for it
     *
     * @param string $name      Names the media, i.e. "banner" or "icon"
     * @param string $path      Path where the file is saved
     * @param string $localFile Path to the local file
     *
     * @return Media
     * @throws Adapter\Exception\FileExistsException
     * @throws Adapter\Exception\FileNotFoundException
     */
    public function write($name, $path, $localFile);

    /**
     * @param Media $media
     *
     * @return string
     * @throws Exception\AdapterNotFoundException
     */
    public function getWebURL(Media $media);
}
