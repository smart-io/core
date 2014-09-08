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
     * @var string
     */
    private $defautName = self::DEFAULT_ENTITY_MANAGER;

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
        $config = $this->registry->getConfig();
        if ($defautName = $config->get('doctrine.default')) {
            $this->defautName = $defautName;
        }
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
    public function setEntityManager($name = null, EntityManager $entityManager)
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
     */
    public function createEntityManager($name = null)
    {
        if (null === $name) {
            $name = $this->getDefautName();
        }

        $config = $this->registry->getConfig();

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

        $doctrineConfig = Setup::createAnnotationMetadataConfiguration(
            $connectionConfig['metadata'],
            $isDevMode
        );

        if (isset($connectionConfig['cache'])) {
            switch ($connectionConfig['cache']) {
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

        if (isset($connectionConfig['proxy_dir'])) {
            $doctrineConfig->setProxyDir($connectionConfig['proxy_dir']);
            if (isset($connectionConfig['proxy_namespace'])) {
                $doctrineConfig->setProxyNamespace($connectionConfig['proxy_namespace']);
            } else {
                $doctrineConfig->setProxyNamespace('Proxies');
            }
        }

        $entityManager = EntityManager::create($connectionConfig, $doctrineConfig);

        $connection = $entityManager->getConnection();
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $this->addListeners($entityManager);
        return $entityManager;
    }
}
