<?php
namespace Sinergi\Core\BrowserSession;

use Sinergi\BrowserSession\BrowserSessionController;
use Sinergi\BrowserSession\CacheDriver\PredisDriver;
use Sinergi\BrowserSession\RouterDriver\KleinDriver;
use Sinergi\Core\ContainerInterface;

class BrowserSession
{
    /**
     * @var BrowserSessionController
     */
    private $controller;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->controller = new BrowserSessionController(
            new DatabaseDriver($container),
            new KleinDriver($container->getKlein()),
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
