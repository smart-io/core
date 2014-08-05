<?php
namespace Sinergi\Core;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

class CommandRuntime implements RuntimeInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var Application
     */
    private $app;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->app = $registry->getConsoleApplication();
    }

    public function configure()
    {
        if (!$this->changeUser()) {
            return;
        }

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
        $this->app->run();
    }

    /**
     * @return bool
     */
    private function changeUser()
    {
        $user = $this->registry->getConfig()->get('command.user');

        if (null !== $user) {
            $name = $user;
            $user = posix_getpwnam($user);
            posix_setgid($user['gid']);
            posix_setuid($user['uid']);

            if (posix_geteuid() !== (int)$user['uid']) {
                $output = new ConsoleOutput();
                $formatter = new FormatterHelper;
                $output->writeln('', true);
                $errorMessages = array('', ' [Error] ', ' Could not change user to ' . $name . ' ', '');
                $formattedBlock = $formatter->formatBlock($errorMessages, 'error');
                $output->writeln($formattedBlock);
                $output->writeln('', true);
                return false;
            }
        }

        return true;
    }
}
