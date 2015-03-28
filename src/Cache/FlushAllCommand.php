<?php

namespace Smart\Core\Cache;

use Sinergi\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Smart\Core\Apc\FlushAllCommand as ApcFlushAllCommand;
use Smart\Core\Redis\FlushAllCommand as RedisFlushAllCommand;
use Smart\Core\Container;

class FlushAllCommand extends Command
{
    const RUN_VAGRANT = true;
    const COMMAND_NAME = 'flushall';

    /**
     * @var ContainerInterface|Container
     */
    private $container;

    /**
     * @param ContainerInterface|Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    /**
     * @return ContainerInterface|Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)->setDescription(
            'Flush all Redis and APC cache'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dummyOutput = new NullOutput();
        $dummyInput = new ArrayInput([]);
        $output->write('Flushing all cache: ');

        (new ApcFlushAllCommand($this->container))->run($dummyInput, $dummyOutput);
        (new RedisFlushAllCommand($this->container))->run($dummyInput, $dummyOutput);

        $output->write('[ <fg=green>DONE</fg=green> ]', true);
    }
}
