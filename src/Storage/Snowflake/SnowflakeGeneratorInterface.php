<?php
namespace App\Storage\Snowflake;

use RuntimeException;

/**
 * Interface for classes which generate Twitter snowflake IDs.
 */
interface SnowflakeGeneratorInterface
{
    /**
     * Generates and returns a unique snowflake integer
     *
     * @return int
     * @throws RuntimeException
     */
    public function generate();
}
