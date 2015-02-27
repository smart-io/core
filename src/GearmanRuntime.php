<?php

namespace Smart\Core;

use Sinergi\Gearman\Command\RestartCommand;
use Sinergi\Gearman\Command\StartCommand;
use Sinergi\Gearman\Command\StopCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class GearmanRuntime implements RuntimeInterface
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
        $errorHandler = new ErrorHandler($this->container->getGearmanLogger());
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
        $command->setConfig($this->container->getGearman()->getConfig());
        $command->setGearmanApplication($this->container->getGearman()->getApplication());
        $command->setProcess($this->container->getGearman()->getProcess());
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
        $command->setConfig($this->container->getGearman()->getConfig());
        $command->setGearmanApplication($this->container->getGearman()->getApplication());
        $command->setProcess($this->container->getGearman()->getProcess());
        $command->run(new ArrayInput([]), new ConsoleOutput());
    }
}
