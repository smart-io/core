<?php
namespace Sinergi\Core\Http;

use Illuminate\Routing\Router;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Sinergi\Core\ContainerInterface;

trait HttpContainerTrait
{
    /**
     * @return ContainerInterface
     */
    abstract function getContainer();

    /**
     * @var Http
     */
    private $httpContainerTraitHttp;

    /**
     * @return Http
     */
    private function getHttp()
    {
        if (null === $this->httpContainerTraitHttp) {
            $this->httpContainerTraitHttp = new Http($this->getContainer());
        }
        return $this->httpContainerTraitHttp;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        if (!$router = $this->getContainer()->get('router')) {
            $router = $this->getHttp()->createRouter();
            $this->getContainer()->set('router', $router);
        }
        return $router;
    }

    /**
     * @param Router $router
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->getContainer()->set('router', $router);
        return $this;
    }

    /**
     * @return RouterApplication
     */
    public function getRouterApplication()
    {
        if (!$routerApplication = $this->getContainer()->get('routerApplication')) {
            $routerApplication = $this->getHttp()->createRouterApplication();
            $this->getContainer()->set('routerApplication', $routerApplication);
        }
        return $routerApplication;
    }

    /**
     * @param RouterApplication $routerApplication
     * @return $this
     */
    public function setRouterApplication(RouterApplication $routerApplication)
    {
        $this->getContainer()->set('routerApplication', $routerApplication);
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if (!$request = $this->getContainer()->get('request')) {
            $request = $this->getHttp()->createRequest();
            $this->getContainer()->set('request', $request);
        }
        return $request;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->getContainer()->set('request', $request);
        return $this;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        if (!$response = $this->getContainer()->get('response')) {
            $response = $this->getHttp()->createResponse();
            $this->getContainer()->set('response', $response);
        }
        return $response;
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->getContainer()->set('response', $response);
        return $this;
    }
}
