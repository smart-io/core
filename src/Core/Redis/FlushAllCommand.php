<?php
namespace Sinergi\Core\Redis;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sinergi\Command;

class FlushAllCommand extends Command
{
    const RUN_VAGRANT = true;
    const COMMAND_NAME = 'redis:flushall';

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
        $this->getRegistry()->getPredis()->getClient()->flushall();
        $output->write('[ <fg=green>DONE</fg=green> ]', true);
    }
}
