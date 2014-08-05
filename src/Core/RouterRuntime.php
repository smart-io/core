<?php
namespace Sinergi\Core;

use Klein\Request;
use Klein\Response;

class RouterRuntime implements RuntimeInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->registry->setRequest(Request::createFromGlobals());
        $this->registry->setResponse(new Response());
    }

    public function configure()
    {
        if ($this->registry->getConfig()->get('app.debug')) {
            error_reporting(E_ALL);
            ini_set('display_errors', "On");
        } else {
            $errorHandler = new ErrorHandler($this->registry->getErrorLogger());
            set_error_handler([$errorHandler, 'error']);
            set_exception_handler([$errorHandler, 'exception']);
            register_shutdown_function([$errorHandler, 'shutdown']);
        }
    }

    public function run()
    {
        $this->registry->getKlein()->dispatch($this->registry->getRequest(), $this->registry->getResponse());
    }
}
