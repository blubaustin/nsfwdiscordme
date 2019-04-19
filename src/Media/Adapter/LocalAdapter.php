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
    public function getName()
    {
        return 'local';
    }

    /**
     * {@inheritDoc}
     */
    public function write($path, $localFile, array $options = [])
    {
        $options = array_merge([
            'mkdir'     => false,
            'overwrite' => false
        ], $options);

        if (!is_readable($localFile)) {
            throw new Exception\FileNotFoundException(
                "File ${localFile} does not exist or is not readable."
            );
        }

        if ($this->exists($path)) {
            if ($options['overwrite']) {
                $this->remove($path);
            } else {
                throw new Exception\FileExistsException(
                    "File ${path} already exists."
                );
            }
        }

        $writePath = $this->getWritePath($path);
        $directory = pathinfo($writePath, PATHINFO_DIRNAME);
        if (!is_writable($directory)) {
            if ($options['mkdir']) {
                if (!@mkdir($directory)) {
                    throw new Exception\WriteException(
                        "Unable to create directory ${directory}."
                    );
                }
            } else {
                throw new Exception\FileNotFoundException(
                    "Directory ${directory} does not exist or is not writable."
                );
            }
        }

        return copy($localFile, $this->getWritePath($path));
    }

    /**
     * {@inheritDoc}
     */
    public function exists($path)
    {
        return is_readable($this->getWritePath($path));
    }

    /**
     * {@inheritDoc}
     */
    public function remove($path)
    {
        $writePath = $this->getWritePath($path);
        if (!is_readable($writePath)) {
            throw new Exception\FileNotFoundException(
                "File ${writePath} does not exist or is not readable."
            );
        }

        return unlink($writePath);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getWritePath($path)
    {
        return sprintf('%s/%s', $this->savePath, trim($path, '/\\'));
    }
}
