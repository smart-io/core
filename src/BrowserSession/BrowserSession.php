<?php

namespace Smart\Core\BrowserSession;

use Sinergi\Container\ContainerInterface;
use Smart\BrowserSession\BrowserSessionController;
use Smart\BrowserSession\CacheDriver\PredisDriver;
use Smart\BrowserSession\DatabaseDriver\DoctrineDriver;
use Smart\BrowserSession\RouterDriver\SmartDriver;
use Smart\Core\Container;

class BrowserSession
{
    /**
     * @var BrowserSessionController
     */
    private $controller;

    /**
     * @param ContainerInterface|Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->controller = new BrowserSessionController(
            new DoctrineDriver($container->getEntityManager()),
            new SmartDriver(
                $container->getRouter(), $container->getRequest(),
                $container->getResponse()
            ),
            new PredisDriver($container->getPredis()->getClient()),
            null,
            true
        );
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param mixed $controller
     *
     * @return $this
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }
}
