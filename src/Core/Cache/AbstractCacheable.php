<?php
namespace Sinergi\Core\Cache;

use Psr\Cache\CacheItemInterface;
use Sinergi\Core\ContainerInterface;
use JMS\Serializer\SerializerBuilder;
use Predis\Client;

abstract class AbstractCacheable implements CacheableInterface, CacheItemInterface
{
    /**
     * @return string
     */
    abstract public function getCacheKey();

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @var array
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

    /**
     * @param string $key
     * @return boolean
     */
    public function exists($key)
    {
        if ($retval = apc_exists($this->getCacheKey() . $key)) {
            return $retval;
        }
        return $this->getClient()->exists($this->getCacheKey() . $key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function fetch($key)
    {
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        if (!$object = apc_fetch($this->getCacheKey() . $key)) {
            $object = $this->getClient()->get($this->getCacheKey() . $key);
        }

        if ($object) {
            $serializer = SerializerBuilder::create()->build();
            $object = $serializer->deserialize(
                $object,
                $this->getType(),
                'json'
            );

            if ($this instanceof CacheableEventsInterface) {
                $object = $this->onFetch($object);
            }

            return $this->items[$key] = $object;
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function store($key, $value)
    {
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($value, 'json');
        apc_store($this->getCacheKey() . $key, $jsonContent);
        $this->getClient()->transaction()->set(
            $this->getCacheKey() . $key,
            $jsonContent
        )->execute();
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        $this->getClient()->transaction()->del([$this->getCacheKey() . $key])->execute();
    }
}
