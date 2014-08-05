<?php
namespace Sinergi\Core;

use Sinergi\Gearman\Command\RestartCommand;
use Sinergi\Gearman\Command\StartCommand;
use Sinergi\Gearman\Command\StopCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class GearmanRuntime implements RuntimeInterface
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
    }

    public function configure()
    {
        $errorHandler = new ErrorHandler($this->registry->getGearmanLogger());
        set_error_handler([$errorHandler, 'error']);
        set_exception_handler([$errorHandler, 'exception']);
        register_shutdown_function([$errorHandler, 'shutdown']);
    }

    public function run()
    {
        $command = $_SERVER['argv'][1];

        if ($command === 'start') {
            $this->startGearman();
        } elseif ($command === 'stop') {
            $this->stopGearman();
        } elseif ($command === 'restart') {
            $this->startGearman(true);
        }
    }

    private function stopGearman()
    {
        $command = new StopCommand();
        $command->setConfig($this->registry->getGearman()->getConfig());
        $command->setGearmanApplication($this->registry->getGearman()->getApplication());
        $command->setProcess($this->registry->getGearman()->getProcess());
        $command->run(new ArrayInput([]), new ConsoleOutput());
    }

    /**
     * @param bool $restart
     */
    private function startGearman($restart = false)
    {
        if (!$restart) {
            $command = new StartCommand();
        } else {
            $command = new RestartCommand();
        }
        $command->setConfig($this->registry->getGearman()->getConfig());
        $command->setGearmanApplication($this->registry->getGearman()->getApplication());
        $command->setProcess($this->registry->getGearman()->getProcess());
        $command->run(new ArrayInput([]), new ConsoleOutput());
    }
}
