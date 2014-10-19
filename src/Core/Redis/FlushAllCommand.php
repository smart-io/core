<?php
namespace Sinergi\Core\Redis;

use Sinergi\Core\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FlushAllCommand extends Command
{
    const RUN_VAGRANT = true;
    const COMMAND_NAME = 'redis:flushall';

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

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)->setDescription(
            'Delete all the keys of all the existing databases, not just the currently selected one. ' .
            'This command never fails.'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Flushing all Redis cache: ');
        $this->container->getPredis()->getClient()->flushall();
        $output->write('[ <fg=green>DONE</fg=green> ]', true);
    }
}
