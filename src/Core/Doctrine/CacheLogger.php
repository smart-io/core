<?php
namespace Sinergi\Core\Doctrine;

use Doctrine\ORM\Cache\CollectionCacheKey;
use Doctrine\ORM\Cache\EntityCacheKey;
use Doctrine\ORM\Cache\Logging\CacheLogger as CacheLoggerInterface;
use Doctrine\ORM\Cache\Logging\StatisticsCacheLogger;
use Doctrine\ORM\Cache\QueryCacheKey;

class CacheLogger extends StatisticsCacheLogger implements CacheLoggerInterface
{
    /**
     * Log an entity put into second level cache.
     *
     * @param string                             $regionName The name of the cache region.
     * @param \Doctrine\ORM\Cache\EntityCacheKey $key        The cache key of the entity.
     */
    public function entityCachePut($regionName, EntityCacheKey $key)
    {
        parent::entityCachePut($regionName, $key);
    }

    /**
     * Log an entity get from second level cache resulted in a hit.
     *
     * @param string                             $regionName The name of the cache region.
     * @param \Doctrine\ORM\Cache\EntityCacheKey $key        The cache key of the entity.
     */
    public function entityCacheHit($regionName, EntityCacheKey $key)
    {
        parent::entityCacheHit($regionName, $key);
    }

    /**
     * Log an entity get from second level cache resulted in a miss.
     *
     * @param string                             $regionName The name of the cache region.
     * @param \Doctrine\ORM\Cache\EntityCacheKey $key        The cache key of the entity.
     */
    public function entityCacheMiss($regionName, EntityCacheKey $key)
    {
        parent::entityCacheMiss($regionName, $key);
    }

    /**
     * Log an entity put into second level cache.
     *
     * @param string                                 $regionName The name of the cache region.
     * @param \Doctrine\ORM\Cache\CollectionCacheKey $key        The cache key of the collection.
     */
    public function collectionCachePut($regionName, CollectionCacheKey $key)
    {
        parent::collectionCachePut($regionName, $key);
    }

    /**
     * Log an entity get from second level cache resulted in a hit.
     *
     * @param string                                 $regionName The name of the cache region.
     * @param \Doctrine\ORM\Cache\CollectionCacheKey $key        The cache key of the collection.
     */
    public function collectionCacheHit($regionName, CollectionCacheKey $key)
    {
        parent::collectionCacheHit($regionName, $key);
    }

    /**
     * Log an entity get from second level cache resulted in a miss.
     *
     * @param string                                 $regionName The name of the cache region.
     * @param \Doctrine\ORM\Cache\CollectionCacheKey $key        The cache key of the collection.
     */
    public function collectionCacheMiss($regionName, CollectionCacheKey $key)
    {
        parent::collectionCacheMiss($regionName, $key);
    }

    /**
     * Log a query put into the query cache.
     *
     * @param string                            $regionName The name of the cache region.
     * @param \Doctrine\ORM\Cache\QueryCacheKey $key        The cache key of the query.
     */
    public function queryCachePut($regionName, QueryCacheKey $key)
    {
        parent::queryCachePut($regionName, $key);
    }

    /**
     * Log a query get from the query cache resulted in a hit.
     *
     * @param string                            $regionName The name of the cache region.
     * @param \Doctrine\ORM\Cache\QueryCacheKey $key        The cache key of the query.
     */
    public function queryCacheHit($regionName, QueryCacheKey $key)
    {
        parent::queryCacheHit($regionName, $key);
    }

    /**
     * Log a query get from the query cache resulted in a miss.
     *
     * @param string                            $regionName The name of the cache region.
     * @param \Doctrine\ORM\Cache\QueryCacheKey $key        The cache key of the query.
     */
    public function queryCacheMiss($regionName, QueryCacheKey $key)
    {
        parent::queryCacheMiss($regionName, $key);
    }
}
