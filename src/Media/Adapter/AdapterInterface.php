<?php
namespace App\Media\Adapter;

/**
 * Interface AdapterInterface
 */
interface AdapterInterface
{
    /**
     * @param string $path      The path where the file will be written
     * @param string $localFile Path to the local file
     *
     * @return bool
     *
     * @throws Exception\FileExistsException
     * @throws Exception\FileNotFoundException
     */
    public function write($path, $localFile);
}
