<?php
namespace App\Tests\Storage\Snowflake;

use App\Storage\Snowflake\RedisGenerator;
use PHPUnit\Framework\TestCase;
use Redis;

/**
 * Class RedisGeneratorTest
 */
class RedisGeneratorTest extends TestCase
{
    /**
     *
     */
    public function testGenerate()
    {
        $redis = new Redis();
        $redis->connect('localhost');
        $generator = new RedisGenerator($redis);

        $actual1 = (string)$generator->generate();
        $this->assertGreaterThanOrEqual(17, strlen($actual1));

        $actual2 = (string)$generator->generate();
        $this->assertGreaterThanOrEqual(17, strlen($actual2));

        $this->assertNotEquals($actual1, $actual2);
    }
}
