<?php
namespace Sinergi\Core\Cache;

use Sinergi\Core\ContainerInterface;
use Sinergi\Core\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Sinergi\Core\Apc\FlushAllCommand as ApcFlushAllCommand;
use Sinergi\Core\Redis\FlushAllCommand as RedisFlushAllCommand;

class FlushAllCommand extends Command
{
    const RUN_VAGRANT = true;
    const COMMAND_NAME = 'flushall';

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
        parent::__construct();
    }

    /**
     * @return ContainerInterface
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
