<?php

namespace Smart\Core\BrowserSession;

use Sinergi\BrowserSession\BrowserSessionController;
use Sinergi\BrowserSession\CacheDriver\PredisDriver;
use Sinergi\BrowserSession\RouterDriver\SinergiDriver;
use Sinergi\Container\ContainerInterface;
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
            new DatabaseDriver($container),
            new SinergiDriver($container->getRouter(), $container->getRequest(), $container->getResponse()),
            new PredisDriver($container->getPredis()->getClient())
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
     * @return $this
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }
}
