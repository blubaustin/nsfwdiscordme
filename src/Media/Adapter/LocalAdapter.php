<?php
namespace App\Media\Adapter;

/**
 * Adapter which writes files to the local file system.
 */
class LocalAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected $savePath;

    /**
     * Constructor
     *
     * @param string $savePath
     */
    public function __construct($savePath)
    {
        $this->setSavePath($savePath);
    }

    /**
     * @param string $savePath
     *
     * @return $this
     */
    public function setSavePath($savePath)
    {
        $this->savePath = $savePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getSavePath()
    {
        return $this->savePath;
    }

    /**
     * {@inheritDoc}
     */
    public function write($path, $localFile)
    {
        if (!is_readable($localFile)) {
            throw new Exception\FileNotFoundException("File ${localFile} does not exist or is not readable.");
        }

        $writePath = sprintf('%s/%s', $this->savePath, trim($path, '/\\'));
        if (file_exists($writePath)) {
            throw new Exception\FileExistsException("File ${writePath} already exists.");
        }

        return copy($localFile, $writePath);
    }
}
