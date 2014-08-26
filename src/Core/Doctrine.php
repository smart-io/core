<?php
namespace Sinergi\Core;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Console\Command\Command as DoctrineCommand;
use Symfony\Component\Console\Helper\HelperSet;
use Exception;

class Doctrine extends AbstractManagerRegistry
{
    const DEFAULT_ENTITY_MANAGER = 'default';

    /**
     * @var EntityManager[]
     */
    private $entityManagers;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var HelperSet
     */
    private $helperSet;

    /**
     * @var DoctrineCommand[]
     */
    private $commands;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
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
     * @param EntityManager $em
     */
    public function addListeners(EntityManager $em)
    {
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
    public function getHelperSet($name = self::DEFAULT_ENTITY_MANAGER)
    {
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
    public function setEntityManager($name = self::DEFAULT_ENTITY_MANAGER, EntityManager $entityManager)
    {
        if ($name === null) {
            $name = self::DEFAULT_ENTITY_MANAGER;
        }

        $this->entityManagers[$name] = $entityManager;
        return $this;
    }

    /**
     * @param string $name
     * @return EntityManager|null
     */
    public function getEntityManager($name = self::DEFAULT_ENTITY_MANAGER)
    {
        if ($name === null) {
            $name = self::DEFAULT_ENTITY_MANAGER;
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
     * @param string $name
     * @return EntityManager|null
     * @throws Exception
     */
    public function createEntityManager($name = self::DEFAULT_ENTITY_MANAGER)
    {
        if ($name === null) {
            $name = self::DEFAULT_ENTITY_MANAGER;
        }

        $config = $this->registry->getConfig()->get('doctrine');
        if (isset($config['connections'][$name])) {
            $conn = $config['connections'][$name];
        } elseif (isset($config['connections'][self::DEFAULT_ENTITY_MANAGER])) {
            $conn = $config['connections'][self::DEFAULT_ENTITY_MANAGER];
        } else {
            throw new Exception("There are no entity manager configurations");
        }

        if ($conn) {
            if (isset($conn['is_dev_mode'])) {
                $isDevMode = (bool)$conn['is_dev_mode'];
            } else {
                $isDevMode = false;
            }

            $doctrineConfig = Setup::createAnnotationMetadataConfiguration(
                $conn['metadata'],
                $isDevMode
            );

            if (isset($conn['cache'])) {
                switch ($conn['cache']) {
                    case 'apc':
                        $cache = new ApcCache;
                        $doctrineConfig->setQueryCacheImpl($cache);
                        $doctrineConfig->setMetadataCacheImpl($cache);
                        break;
                    case 'array':
                        $cache = new ArrayCache;
                        $doctrineConfig->setQueryCacheImpl($cache);
                        $doctrineConfig->setMetadataCacheImpl($cache);
                        break;
                }
            }

            if (isset($conn['proxy_dir'])) {
                $doctrineConfig->setProxyDir($conn['proxy_dir']);
                if (isset($conn['proxy_namespace'])) {
                    $doctrineConfig->setProxyNamespace($conn['proxy_namespace']);
                } else {
                    $doctrineConfig->setProxyNamespace('Proxies');
                }
            }

            $em = EntityManager::create($conn, $doctrineConfig);

            $conn = $em->getConnection();
            $conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

            $this->addListeners($em);
            return $em;
        }
        return null;
    }
}
