<?php
namespace App\Storage\Sessions;

use Redis;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;

/**
 * Class RedisSessionHandler
 */
class RedisSessionHandler extends AbstractSessionHandler
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var int
     */
    protected $database = 0;

    /**
     * Constructor
     *
     * @param Redis $redis
     * @param array $options
     */
    public function __construct($redis, array $options = [])
    {
        $this->redis    = $redis;
        $this->prefix   = $options['prefix'] ?? 'sf_s';
        $this->database = $options['database'] ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRead($sessionId): string
    {
        $this->redis->select($this->database);
        return $this->redis->get($this->prefix . $sessionId) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($sessionId, $data): bool
    {
        $this->redis->select($this->database);
        $result = $this->redis->setEx($this->prefix . $sessionId, (int)ini_get('session.gc_maxlifetime'), $data);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDestroy($sessionId): bool
    {
        $this->redis->select($this->database);
        $this->redis->del($this->prefix . $sessionId);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $data)
    {
        $this->redis->select($this->database);
        return (bool)$this->redis->expire($this->prefix . $sessionId, (int)ini_get('session.gc_maxlifetime'));
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime): bool
    {
        return true;
    }
}
