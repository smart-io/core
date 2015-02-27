<?php

namespace Smart\Core\Http;

use Router\Router;
use Router\Request;
use Router\Response;
use Smart\Core\ContainerInterface;

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
        return new Router();
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
        $request = Request::createFromGlobals();
        $this->container->getRouter()->request($request);
        return $request;
    }

    /**
     * @return Response
     */
    public function createResponse()
    {
        $response = new Response;
        $this->container->getRouter()->setResponse($response);
        return $response;
    }
}
