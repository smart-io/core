<?php
namespace Sinergi\Core\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Sinergi\EmailQueue\Doctrine\MappingListener;

class ListenerInstanciator
{
    /**
     * @param EntityManagerInterface $entityManager
     */
    public function instanciate(EntityManagerInterface $entityManager)
    {
        $evm = $entityManager->getEventManager();
        $evm->addEventListener('loadClassMetadata', new MappingListener());
    }
}
