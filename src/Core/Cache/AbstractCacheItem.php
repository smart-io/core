<?php
namespace Sinergi\Core\Cache;

use DateTime;
use Psr\Cache\CacheItemInterface;
use JMS\Serializer\SerializerBuilder;

abstract class AbstractCacheItem implements CacheItemInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var DateTime
     */
    protected $expiration;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        $serializer = SerializerBuilder::create()->build();

        $item = $serializer->deserialize(
            (string)$this->value,
            $this->getType(),
            'json'
        );

        if ($this instanceof CacheItemEventsInterface) {
            $item = $this->onGet($item);
        }

        return $item;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function set($value)
    {
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($value, 'json');
        $this->value = $jsonContent;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isHit()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function exists()
    {
        return true;
    }

    /**
     * @param DateTime $ttl
     * @return $this
     */
    public function setExpiration($ttl = null)
    {
        if (!$ttl instanceof DateTime) {
            throw new \InvalidArgumentException;
        }

        $this->expiration = $ttl;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getExpiration()
    {
        return $this->expiration;
    }
}
