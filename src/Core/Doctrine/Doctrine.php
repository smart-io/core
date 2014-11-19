<?php
namespace Sinergi\Core\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Cache\RegionsConfiguration;
use Doctrine\ORM\Cache\CacheFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Setup;
use Sinergi\Core\BackgroundPersister\BackgroundPersister;
use Sinergi\Core\ContainerInterface;
use Sinergi\Core\Doctrine\CacheLogger;
use Sinergi\Core\Doctrine\ListenerInstanciator;
use Symfony\Component\Console\Command\Command as DoctrineCommand;
use Symfony\Component\Console\Helper\HelperSet;
use Exception;
use Doctrine\Common\Cache\Cache;
use Sinergi\Core\Doctrine\SqlLogger;
use Sinergi\Core\Command;

class Doctrine extends AbstractManagerRegistry
{
    const DEFAULT_ENTITY_MANAGER = 'default';

    /**
     * @var string
     */
    private $defautName = self::DEFAULT_ENTITY_MANAGER;

    /**
     * @var EntityManager[]
     */
    private $entityManagers;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var HelperSet
     */
    private $helperSet;

    /**
     * @var DoctrineCommand[]
     */
    private $commands;

    /**
     * @var BackgroundPersister
     */
    private $backgroundPersister;

    /**
     * @var SqlLogger
     */
    private $sqlLogger;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var CacheFactory
     */
    private $cacheFactory;

    /**
     * @var CacheLogger
     */
    private $cacheLogger;

    /**
     * @var RegionsConfiguration
     */
    private $regionsConfiguration;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $config = $this->container->getConfig();
        if ($defautName = $config->get('doctrine.default')) {
            $this->defautName = $defautName;
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param $entity
     * @return bool|null
     */
    public function persistBackground(EntityManagerInterface $entityManager, $entity)
    {
        if (null === $this->backgroundPersister) {
            $this->backgroundPersister = new BackgroundPersister($this->container);
        }
        $this->backgroundPersister->persist($entityManager, $entity);
        return true;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param $entity
     * @return bool|null
     */
    public function mergeBackground(EntityManagerInterface $entityManager, $entity)
    {
        if (null === $this->backgroundPersister) {
            $this->backgroundPersister = new BackgroundPersister($this->container);
        }
        $this->backgroundPersister->merge($entityManager, $entity);
        return true;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param $entity
     * @return bool|null
     */
    public function mergeOrPersistBackground(EntityManagerInterface $entityManager, $entity)
    {
        if (null === $this->backgroundPersister) {
            $this->backgroundPersister = new BackgroundPersister($this->container);
        }
        $this->backgroundPersister->mergeOrPersist($entityManager, $entity);
        return true;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @return null|string
     */
    public function getEntityManagerKey(EntityManagerInterface $entityManager)
    {
        foreach ($this->entityManagers as $entityManagerKey => $entityManagerObject) {
            if ($entityManager === $entityManagerObject) {
                return $entityManagerKey;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getService($name)
    {
        return $this->getEntityManager($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function resetService($name)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasNamespace($alias)
    {
        foreach ($this->entityManagers as $entityManager) {
            try {
                return $entityManager->getConfiguration()->getEntityNamespace($alias);
            } catch (ORMException $e) {
            }
        }

        throw ORMException::unknownEntityNamespace($alias);
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function addListeners(EntityManagerInterface $entityManager)
    {
        (new ListenerInstanciator($this->container))->instanciate($entityManager);
    }

    private function addDefaultCommands()
    {
        $this->commands = [];
    }

    /**
     * @param DoctrineCommand $command
     * @return $this
     */
    public function addCommand(DoctrineCommand $command)
    {
        if (null === $this->commands) {
            $this->addDefaultCommands();
        }
        $this->commands[] = $command;
        return $this;
    }

    /**
     * @return Command[]
     */
    public function getCommands()
    {
        if (null === $this->commands) {
            $this->addDefaultCommands();
        }
        return $this->commands;
    }

    /**
     * @param string $name
     * @return HelperSet
     */
    public function getHelperSet($name = null)
    {
        if (null === $name) {
            $name = $this->getDefautName();
        }

        if (null === $this->helperSet) {
            $this->helperSet = ConsoleRunner::createHelperSet($this->getEntityManager($name));
        }
        return $this->helperSet;
    }

    /**
     * @param string $name
     * @param EntityManager $entityManager
     * @return $this
     */
    public function setEntityManager($name = null, EntityManager $entityManager = null)
    {
        if (null === $name) {
            $name = $this->getDefautName();
        }

        $this->entityManagers[$name] = $entityManager;
        return $this;
    }

    /**
     * @param string $name
     * @return EntityManager|null
     */
    public function getEntityManager($name = null)
    {
        if (null === $name) {
            $name = $this->getDefautName();
        }

        if (isset($this->entityManagers[$name])) {
            return $this->entityManagers[$name];
        }

        $entityManager = $this->createEntityManager($name);
        if ($entityManager) {
            $this->entityManagers[$name] = $entityManager;
            return $entityManager;
        }

        return null;
    }

    /**
     * @return string
     */
    private function getDefautName()
    {
        return $this->defautName;
    }

    /**
     * @param string $name
     * @return EntityManager|null
     * @throws Exception
     * todo: split into chunks
     */
    public function createEntityManager($name = null)
    {
        if (null === $name) {
            $name = $this->getDefautName();
        }

        $config = $this->container->getConfig();

        if ($config->get("doctrine.connections.{$name}")) {
            $connectionConfig = $config->get("doctrine.connections.{$name}");
        } elseif ($config->get("doctrine.connections." . $this->getDefautName())) {
            $connectionConfig = $config->get("doctrine.connections." . $this->getDefautName());
        } else {
            throw new Exception("There are no entity manager configurations");
        }

        if (isset($connectionConfig['is_dev_mode'])) {
            $isDevMode = (bool)$connectionConfig['is_dev_mode'];
        } else {
            $isDevMode = false;
        }

        $doctrineConfig = Setup::createConfiguration($isDevMode);

        $doctrineConfig->setMetadataDriverImpl(
            new AnnotationDriver(new AnnotationReader(), $connectionConfig['paths'])
        );

        $doctrineConfig->setQuoteStrategy(new DefaultQuoteStrategy());

        if (isset($connectionConfig['cache'])) {
            switch ($connectionConfig['cache']) {
                case 'apc':
                    $this->cache = new ApcCache;
                    $doctrineConfig->setQueryCacheImpl($this->cache);
                    $doctrineConfig->setMetadataCacheImpl($this->cache);
                    $doctrineConfig->setHydrationCacheImpl($this->cache);
                    $doctrineConfig->setResultCacheImpl($this->cache);
                    break;
                case 'array':
                    $this->cache = new ArrayCache;
                    $doctrineConfig->setQueryCacheImpl($this->cache);
                    $doctrineConfig->setMetadataCacheImpl($this->cache);
                    $doctrineConfig->setHydrationCacheImpl($this->cache);
                    $doctrineConfig->setResultCacheImpl($this->cache);
                    break;
            }

            if (
                null !== $this->cache &&
                isset($connectionConfig['second_level_cache']) &&
                $connectionConfig['second_level_cache'] === true
            ) {
                throw new Exception("Doctrine Second Level Cache does not work so don't bother");

                $this->regionsConfiguration = new RegionsConfiguration();
                $this->regionsConfiguration->setLifetime('my_entity_region', 3600);
                $this->cacheFactory = new DefaultCacheFactory($this->regionsConfiguration, $this->cache);
                $doctrineConfig->setSecondLevelCacheEnabled();
                $secondLevelCacheConfiguration = $doctrineConfig->getSecondLevelCacheConfiguration();
                $secondLevelCacheConfiguration->setCacheFactory($this->cacheFactory);
                $secondLevelCacheConfiguration->setRegionsConfiguration($this->regionsConfiguration);
                if (isset($connectionConfig['debug']) && $connectionConfig['debug'] === true) {
                    $this->cacheLogger = new CacheLogger();
                    $secondLevelCacheConfiguration->setCacheLogger($this->cacheLogger);
                }
            }
        }

        if (isset($connectionConfig['proxy_dir'])) {
            $doctrineConfig->setProxyDir($connectionConfig['proxy_dir']);
            if (isset($connectionConfig['proxy_namespace'])) {
                $doctrineConfig->setProxyNamespace($connectionConfig['proxy_namespace']);
            } else {
                $doctrineConfig->setProxyNamespace('Proxies');
            }
        }

        if (!isset($connectionConfig['charset'])) {
            $connectionConfig['charset'] = 'utf8';
        }
        $entityManager = EntityManager::create($connectionConfig, $doctrineConfig);

        $connection = $entityManager->getConnection();
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        if (isset($connectionConfig['debug']) && $connectionConfig['debug'] === true) {
            $this->sqlLogger = new SqlLogger($this->container);
            $connection->getConfiguration()->setSQLLogger($this->sqlLogger);
        }

        $this->addListeners($entityManager);
        return $entityManager;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param Cache $cache
     * @return $this
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @return CacheFactory
     */
    public function getCacheFactory()
    {
        return $this->cacheFactory;
    }

    /**
     * @param CacheFactory $cacheFactory
     * @return $this
     */
    public function setCacheFactory(CacheFactory $cacheFactory)
    {
        $this->cacheFactory = $cacheFactory;
        return $this;
    }

    /**
     * @return SqlLogger
     */
    public function getSqlLogger()
    {
        if (null === $this->sqlLogger) {
            $this->createEntityManager();
        }
        return $this->sqlLogger;
    }

    /**
     * @param SqlLogger $sqlLogger
     * @return $this
     */
    public function setSqlLogger(SqlLogger $sqlLogger)
    {
        $this->sqlLogger = $sqlLogger;
        return $this;
    }

    /**
     * @return CacheLogger
     */
    public function getCacheLogger()
    {
        return $this->cacheLogger;
    }

    /**
     * @param CacheLogger $cacheLogger
     * @return $this
     */
    public function setCacheLogger(CacheLogger $cacheLogger)
    {
        $this->cacheLogger = $cacheLogger;
        return $this;
    }

    /**
     * @return RegionsConfiguration
     */
    public function getRegionsConfiguration()
    {
        return $this->regionsConfiguration;
    }

    /**
     * @param RegionsConfiguration $regionsConfiguration
     * @return $this
     */
    public function setRegionsConfiguration(RegionsConfiguration $regionsConfiguration)
    {
        $this->regionsConfiguration = $regionsConfiguration;
        return $this;
    }
}
