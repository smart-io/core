<?php

namespace Smart\Core\Cache;

use DateTime;
use Predis\Client;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Smart\Core\ContainerInterface;

abstract class AbstractCacheItemPool implements CacheItemPoolInterface
{
    abstract function getKey();

    /**
     * @var string
     */
    protected $key;

    /**
     * @var CacheItemInterface[]
     */
    protected $items = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $key
     * @return \Psr\Cache\CacheItemInterface
     */
    public function getItem($key)
    {
        if (isset($this->items[$key]) && $this->items[$key]->getExpiration() <= new DateTime()) {
            return $this->items[$key];
        }

        if (!$item = apc_fetch($this->getKey() . $key)) {
            $item = $this->getClient()->get($this->getKey() . $key);
        }

        if ($item) {
            $item = unserialize($item);

            if ($item instanceof CacheItemInterface && $item->getExpiration() <= new DateTime()) {
                $this->items[$item->getKey()] = $item;
                return $item;
            }
        }

        return null;
    }

    /**
     * @param array $keys
     * @return array|\Traversable
     */
    public function getItems(array $keys = [])
    {
        $retval = [];
        foreach ($keys as $key) {
            $retval[$key] = $this->getItem($key);
        }

        return $retval;
    }

    /**
     * @return boolean
     */
    public function clear()
    {
        $this->items = [];
        return true;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function deleteItem($key)
    {
        apc_delete($this->getKey() . $key);
        $this->getClient()->transaction()->del([$this->getKey() . $key])->execute();
        return $this;
    }

    /**
     * @param array $keys
     * @return $this
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }
        return $this;
    }

    /**
     * @param CacheItemInterface $item
     * @return $this
     */
    public function save(CacheItemInterface $item)
    {
        $serialized = serialize($item);
        apc_store($this->getKey() . $item->getKey(), $serialized);
        $this->getClient()->transaction()->set(
            $this->getKey() . $item->getKey(),
            $serialized
        )->execute();
        return $this;
    }

    /**
     * @param CacheItemInterface $item
     * @return static
     * @throws \Exception
     * @internal
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        throw new \Exception('saveDeferred is not implemented');
    }

    /**
     * @return bool
     * @throws \Exception
     * @internal
     */
    public function commit()
    {
        throw new \Exception('saveDeferred is not implemented');
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function exists($key)
    {
        if ($retval = apc_exists($this->getKey() . $key)) {
            return $retval;
        }
        return $this->getClient()->exists($this->getKey() . $key);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if (null === $this->client) {
            $this->client = $this->container->getPredis()->getClient();
        }
        return $this->client;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }
}
