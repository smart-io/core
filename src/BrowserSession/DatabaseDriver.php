<?php

namespace Smart\Core\BrowserSession;

use Sinergi\Container\ContainerInterface;
use Smart\BrowserSession\BrowserSessionEntity;
use Smart\BrowserSession\DatabaseDriver\DoctrineDriver;
use Smart\Core\Container;

class DatabaseDriver extends DoctrineDriver
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
        parent::__construct($container->getEntityManager());
    }

    /**
     * @return boolean
     */
    public function isBackgroundDriver()
    {
        return true;
    }

    /**
     * @param BrowserSessionEntity $browserSession
     */
    public function mergeOrPersistBackground(
        BrowserSessionEntity $browserSession
    ) {
        $this->container->getDoctrine()
            ->mergeOrPersistBackground($this->getEntityManager(),
                $browserSession);
    }

    /**
     * @param BrowserSessionEntity $browserSession
     */
    public function mergeOrPersist(BrowserSessionEntity $browserSession)
    {
    }
}
