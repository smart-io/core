<?php

namespace Smart\Core\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Sinergi\Container\ContainerInterface;
use Smart\Core\EmailQueue\Config;
use Smart\EmailQueue\Database\Doctrine\MappingListener as EmailQueueMappingListener;
use Smart\Core\Container;

class ListenerInstanciator
{
    /**
     * @var ContainerInterface|Container
     */
    private $container;

    /**
     * @param ContainerInterface|Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function instanciate(EntityManagerInterface $entityManager)
    {
        $evm = $entityManager->getEventManager();
        $evm->addEventListener('loadClassMetadata', new EmailQueueMappingListener(
            new Config($this->container->getConfig())
        ));
    }
}
