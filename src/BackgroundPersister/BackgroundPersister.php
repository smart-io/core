<?php

namespace Smart\Core\BackgroundPersister;

use Doctrine\ORM\EntityManagerInterface;
use Sinergi\Container\ContainerInterface;
use Smart\Core\Container;

// todo rename to database deferrer
class BackgroundPersister
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $persist = [];

    /**
     * @var array
     */
    private $merge = [];

    /**
     * @var array
     */
    private $mergeOrPersist = [];

    /**
     * @param ContainerInterface|Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __destruct()
    {
        $persist = [];
        foreach ($this->persist as $entityManagerKey => $entityManagerEntities) {
            foreach ($entityManagerEntities as $entity) {
                $persist[] = [
                    'entityManagerKey' => $entityManagerKey,
                    'entity' => serialize($entity)
                ];
            }
        }

        $merge = [];
        foreach ($this->merge as $entityManagerKey => $entityManagerEntities) {
            foreach ($entityManagerEntities as $entity) {
                $merge[] = [
                    'entityManagerKey' => $entityManagerKey,
                    'entity' => serialize($entity)
                ];
            }
        }

        $mergeOrPersist = [];
        foreach ($this->mergeOrPersist as $entityManagerKey => $entityManagerEntities) {
            foreach ($entityManagerEntities as $entity) {
                $mergeOrPersist[] = [
                    'entityManagerKey' => $entityManagerKey,
                    'entity' => serialize($entity)
                ];
            }
        }

        $this->container->getGearmanDispatcher()->background(
            BackgroundPersisterJob::JOB_NAME,
            [
                'persist' => $persist,
                'merge' => $merge,
                'mergeOrPersist' => $mergeOrPersist,
            ]
        );
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param object $entity
     */
    public function persist(EntityManagerInterface $entityManager, $entity)
    {
        $entityManager->detach($entity);
        $entityManagerKey = $this->container->getDoctrine()->getEntityManagerKey($entityManager);
        $this->persist[$entityManagerKey][spl_object_hash($entity)] = $entity;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param object $entity
     */
    public function merge(EntityManagerInterface $entityManager, $entity)
    {
        $entityManager->detach($entity);
        $entityManagerKey = $this->container->getDoctrine()->getEntityManagerKey($entityManager);
        $this->merge[$entityManagerKey][spl_object_hash($entity)] = $entity;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param object $entity
     */
    public function mergeOrPersist(EntityManagerInterface $entityManager, $entity)
    {
        $entityManager->detach($entity);
        $entityManagerKey = $this->container->getDoctrine()->getEntityManagerKey($entityManager);
        $this->mergeOrPersist[$entityManagerKey][spl_object_hash($entity)] = $entity;
    }
}
