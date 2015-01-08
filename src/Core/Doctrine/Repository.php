<?php
namespace Sinergi\Core\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sinergi\Core\ContainerInterface;

class Repository extends EntityRepository
{
    /**
     * @var array
     */
    protected $repositoriesContainer = [];

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @param EntityManagerInterface $entityManager
     * @param null $entityClass
     */
    public function __construct(
        $container = null,
        $entityManager = null,
        $entityClass = null
    )
    {
        if ($container instanceof EntityManagerInterface) {
            parent::__construct(
                $container,
                $entityManager
            );
            return;
        }

        $this->container = $container;
        if (null !== $entityManager) {
            $this->entityManager = $entityManager;
        } else {
            $this->entityManager = $container->getEntityManager();
        }
        $this->_em = $this->entityManager;
        if (null !== $entityClass) {
            parent::__construct(
                $this->entityManager,
                $this->entityManager->getMetadataFactory()->getMetadataFor($entityClass)
            );
        }
    }

    /**
     * @param string $entityClass
     * @param Repository|EntityRepository $repository
     */
    public function add($entityClass, $repository)
    {
        $this->repositoriesContainer[$entityClass] = $repository;
    }

    /**
     * @param string $entityClass
     * @return bool
     */
    public function exists($entityClass)
    {
        return isset($this->repositoriesContainer[$entityClass]);
    }

    /**
     * @param string $entityClass
     * @return Repository|EntityRepository
     */
    public function get($entityClass)
    {
        return $this->repositoriesContainer[$entityClass];
    }

    /**
     * @param string $entityClass
     * @param null|string $repositoryClass
     */
    public function create($entityClass, $repositoryClass = null)
    {
        if (null !== $repositoryClass) {
            if (is_subclass_of($repositoryClass, Repository::class)) {
                $repository = new $repositoryClass(
                    $this->container,
                    $this->getEntityManager(),
                    $entityClass
                );
            } else {
                $repository = new $repositoryClass(
                    $this->getEntityManager(),
                    $this->getEntityManager()->getMetadataFactory()->getMetadataFor($entityClass)
                );
            }
        } else {
            $repository = $this->getEntityManager()->getRepository($entityClass);
        }
        $this->add($entityClass, $repository);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }
}
