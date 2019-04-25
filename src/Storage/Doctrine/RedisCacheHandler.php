<?php
namespace App\Storage\Doctrine;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Redis;

/**
 * Class RedisCacheHandler
 */
class RedisCacheHandler extends CacheProvider
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
        $this->prefix   = $options['prefix'] ?? 'doctrine_';
        $this->database = $options['database'] ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetch($id)
    {
        $this->redis->select($this->database);
        $result = $this->redis->get($this->prefix . $id);
        if ($result === false) {
            return false;
        }

        return unserialize($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetchMultiple(array $keys)
    {
        $this->redis->select($this->database);

        $prefixedKeys = [];
        foreach($keys as $key) {
            $prefixedKeys[] = $this->prefix . $key;
        }
        $fetchedItems = array_combine($keys, $this->redis->mget($prefixedKeys));

        // Redis mget returns false for keys that do not exist. So we need to filter those
        // out unless it's the real data.
        $foundItems = [];
        foreach ($fetchedItems as $key => $value) {
            if ($value === false && !$this->redis->exists($this->prefix . $key)) {
                continue;
            }
            $foundItems[$key] = unserialize($value);
        }

        return $foundItems;
    }

    /**
     * {@inheritDoc}
     */
    protected function doContains($id)
    {
        $this->redis->select($this->database);
        $exists = $this->redis->exists($this->prefix . $id);
        if (is_bool($exists)) {
            return $exists;
        }

        return $exists > 0;
    }

    /**
     * {@inheritDoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $data = serialize($data);
        $this->redis->select($this->database);
        if ($lifeTime > 0) {
            return $this->redis->setex($this->prefix . $id, $lifeTime, $data);
        }

        return $this->redis->set($this->prefix . $id, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0)
    {
        $this->redis->select($this->database);
        foreach($keysAndValues as $key => &$value) {
            $value = serialize($value);
        }

        if ($lifetime) {
            $success = true;

            // Keys have lifetime, use SETEX for each of them
            foreach ($keysAndValues as $key => $value) {
                if ($this->redis->setex($this->prefix . $key, $lifetime, $value)) {
                    continue;
                }
                $success = false;
            }

            return $success;
        }

        $prefixed = [];
        foreach($keysAndValues as $key => $value) {
            $prefixed[$this->prefix . $key] = $value;
        }

        return (bool) $this->redis->mset($prefixed);
    }

    /**
     * {@inheritDoc}
     */
    protected function doDelete($id)
    {
        $this->redis->select($this->database);

        return $this->redis->delete($this->prefix . $id) >= 0;
    }

    /**
     * {@inheritDoc}
     */
    protected function doFlush()
    {
        $this->redis->select($this->database);

        return $this->redis->flushDB();
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetStats()
    {
        $this->redis->select($this->database);
        $info = $this->redis->info();

        return [
            Cache::STATS_HITS              => $info['keyspace_hits'],
            Cache::STATS_MISSES            => $info['keyspace_misses'],
            Cache::STATS_UPTIME            => $info['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE      => $info['used_memory'],
            Cache::STATS_MEMORY_AVAILABLE  => false,
        ];
    }
}
