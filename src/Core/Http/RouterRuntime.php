<?php
namespace Sinergi\Core\Http;

use Sinergi\Core\ContainerInterface;
use Sinergi\Core\ErrorHandler;
use Sinergi\Core\RuntimeInterface;

class RouterRuntime implements RuntimeInterface
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

    public function configure()
    {
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
        $this->container->getResponse()->send();
    }
}
