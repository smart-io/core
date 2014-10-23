<?php
namespace Sinergi\Core\Http;

use Illuminate\Routing\Router;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Sinergi\Core\ContainerInterface;

class Http
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
     * @return Router
     */
    public function createRouter()
    {
        return new Router(new EventDispatcher());
    }

    /**
     * @return Router
     */
    public function createRouterApplication()
    {
        return new RouterApplication($this->container);
    }

    /**
     * @return Request
     */
    public function createRequest()
    {
        return Request::createFromBase(
            \Symfony\Component\HttpFoundation\Request::createFromGlobals()
        );
    }

    /**
     * @return Response
     */
    public function createResponse()
    {
        return new Response();
    }
}
