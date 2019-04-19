<?php
namespace App\Storage\Snowflake;

use Redis;
use RuntimeException;

/**
 * Class RedisGenerator
 */
class RedisGenerator implements SnowflakeGeneratorInterface
{
    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var int
     */
    protected $database = 0;

    /**
     * Constructor
     *
     * @param Redis $redis
     * @param int   $database
     */
    public function __construct(Redis $redis, $database = 0)
    {
        $this->redis    = $redis;
        $this->database = $database;
    }

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        $this->redis->select($this->database);

        $sequence    = $this->redis->incr('snowflake');
        $time        = (int)(microtime(true) * 1000);
        $machine     = getenv('SNOWFLAKE_MACHINE_ID') ?: 0;
        $binTime     = str_pad(decbin($time), 41, 0, STR_PAD_LEFT);
        $binMachine  = str_pad(decbin($machine), 13, 0, STR_PAD_LEFT);
        $binSequence = str_pad(decbin($sequence), 9, 0, STR_PAD_LEFT);
        $id          = bindec($binTime . $binMachine . $binSequence);

        if (!is_int($id)) {
            throw new RuntimeException('The bits of integer is larger than PHP_INT_MAX');
        }

        return $id;
    }
}
