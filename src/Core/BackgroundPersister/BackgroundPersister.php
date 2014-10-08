<?php
namespace Sinergi\Core\BackgroundPersister;

use Doctrine\ORM\EntityManagerInterface;
use Sinergi\Core\RegistryInterface;

class BackgroundPersister
{
    /**
     * @var RegistryInterface
     */
    private $registry;

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
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
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

        $this->registry->getGearmanDispatcher()->background(
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
        $entityManagerKey = $this->registry->getDoctrine()->getEntityManagerKey($entityManager);
        $this->persist[$entityManagerKey][spl_object_hash($entity)] = $entity;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param object $entity
     */
    public function merge(EntityManagerInterface $entityManager, $entity)
    {
        $entityManager->detach($entity);
        $entityManagerKey = $this->registry->getDoctrine()->getEntityManagerKey($entityManager);
        $this->merge[$entityManagerKey][spl_object_hash($entity)] = $entity;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param object $entity
     */
    public function mergeOrPersist(EntityManagerInterface $entityManager, $entity)
    {
        $entityManager->detach($entity);
        $entityManagerKey = $this->registry->getDoctrine()->getEntityManagerKey($entityManager);
        $this->mergeOrPersist[$entityManagerKey][spl_object_hash($entity)] = $entity;
    }
}
