<?php
namespace Sinergi\Core\BackgroundPersister;

use Doctrine\ORM\EntityManager;
use GearmanJob;
use Sinergi\Core\ContainerInterface;
use Sinergi\Core\Job;

// todo rename to database deferrer
class BackgroundPersisterJob extends Job
{
    const JOB_NAME = 'backgroundpersister';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::JOB_NAME;
    }

    /**
     * @param GearmanJob|null $job
     * @return mixed
     */
    public function execute(GearmanJob $job = null)
    {
        $data = unserialize($job->workload());
        if (isset($data['persist'])) {
            foreach ($data['persist'] as $data) {
                $entityManager = $this->getEntityManager($data['entityManagerKey']);
                $entity = unserialize($data['entity']);
                $this->persist($entityManager, $entity);
            }
        }
        if (isset($data['merge'])) {
            foreach ($data['merge'] as $data) {
                $entityManager = $this->getEntityManager($data['entityManagerKey']);
                $entity = unserialize($data['entity']);
                $this->merge($entityManager, $entity);
            }
        }
        if (isset($data['mergeOrPersist'])) {
            foreach ($data['mergeOrPersist'] as $data) {
                $entityManager = $this->getEntityManager($data['entityManagerKey']);
                $entity = unserialize($data['entity']);
                $this->mergeOrPersist($entityManager, $entity);
            }
        }
    }

    /**
     * @param EntityManager $entityManager
     * @param object $entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function persist(EntityManager $entityManager, $entity)
    {
        try {
            $entityManager->flush();
            $entityManager->clear();
            $entityManager->persist($entity);
            $entityManager->flush();
        } catch (\Exception $e) {
            $this->getContainer()->getDoctrine()->getSqlLogger()->error($e->getMessage());
        }
    }

    /**
     * @param EntityManager $entityManager
     * @param object $entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function merge(EntityManager $entityManager, $entity)
    {
        try {
            $entityManager->flush();
            $entityManager->clear();
            $entityManager->merge($entity);
            $entityManager->flush();
        } catch (\Exception $e) {
            $this->getContainer()->getDoctrine()->getSqlLogger()->error($e->getMessage());
        }
    }

    /**
     * @param EntityManager $entityManager
     * @param object $entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function mergeOrPersist(EntityManager $entityManager, $entity)
    {
        try {
            $entityManager->flush();
            $entityManager->clear();
            $entityManager->merge($entity);
            $entityManager->flush();
        } catch (\Exception $e) {
            try {
                $entityManager->persist($entity);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->getContainer()->getDoctrine()->getSqlLogger()->error($e->getMessage());
            }
        }
    }
}
