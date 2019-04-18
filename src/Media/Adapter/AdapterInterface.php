<?php
namespace App\Media\Adapter;

/**
 * Interface AdapterInterface
 */
interface AdapterInterface
{
    /**
     * Returns the name of the adapter, i.e. "local" or "aws"
     *
     * @return string
     */
    public function getName();

    /**
     * Writes the local file to the given path
     *
     * @param string $path      The path where the file will be written
     * @param string $localFile Path to the local file
     * @param bool   $overwrite Overwrite the file if it already exists
     *
     * @return bool
     *
     * @throws Exception\FileExistsException
     * @throws Exception\FileNotFoundException
     */
    public function write($path, $localFile, $overwrite = false);

    /**
     * Returns a boolean indicating whether the given file exists
     *
     * @param string $path The path to test
     *
     * @return bool
     */
    public function exists($path);

    /**
     * Deletes the file at the given path
     *
     * @param string $path Path to the file
     *
     * @return bool
     *
     * @throws Exception\FileNotFoundException
     */
    public function remove($path);
}
