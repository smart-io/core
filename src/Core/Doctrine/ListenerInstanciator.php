<?php
namespace Sinergi\Core\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Sinergi\Core\ContainerInterface;
use Sinergi\Core\EmailQueue\Config;
use Sinergi\EmailQueue\Doctrine\MappingListener as EmailQueueMappingListener;

class ListenerInstanciator
{
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
