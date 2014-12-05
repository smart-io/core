<?php
namespace Sinergi\Core\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sinergi\Core\ContainerInterface;

class Repository extends EntityRepository
{
    protected $repositoriesContainer = [];

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
        ContainerInterface $container,
        EntityManagerInterface $entityManager = null,
        $entityClass = null
    )
    {
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

    public function exists($entityClass)
    {
        return isset($this->repositoriesContainer[$entityClass]);
    }

    public function get($entityClass)
    {
        return $this->repositoriesContainer[$entityClass];
    }

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
}
