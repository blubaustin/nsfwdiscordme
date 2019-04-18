<?php
namespace App\Media\Adapter\Exception;

/**
 * Thrown when trying to write a file to a path which already exists.
 */
class FileExistsException extends Exception {}
