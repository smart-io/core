<?php

namespace Smart\Core;

use Sinergi\Container\ContainerInterface;

class RouterRuntime implements RuntimeInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function configure()
    {
        $config = $this->container->getConfig();
        if ($config->get('app.timezone')) {
            date_default_timezone_set($config->get('app.timezone'));
        }

        if ($this->container->getConfig()->get('app.debug')) {
            error_reporting(E_ALL);
            ini_set('display_errors', "On");
        } else {
            $errorHandler = new ErrorHandler($this->container->getErrorLogger());
            set_error_handler([$errorHandler, 'error']);
            set_exception_handler([$errorHandler, 'exception']);
            register_shutdown_function([$errorHandler, 'shutdown']);
        }
    }

    public function run()
    {
        $this->container->getRouter()->dispatch(
            $this->container->getRequest(),
            $this->container->getResponse()
        );
    }
}
