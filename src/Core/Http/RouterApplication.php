<?php
namespace Sinergi\Core\Http;

use Sinergi\Core\ContainerInterface;

class RouterApplication
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
     * @param RouterInterface $router
     */
    public function add(RouterInterface $router)
    {
        $router->init($this->container);
    }

    public function dispatch()
    {
        $this->container->getRouter()->dispatch($this->container->getRequest());
    }
}
