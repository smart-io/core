<?php
namespace Sinergi\Core\Cache;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Sinergi\Command;
use Sinergi\Core\Apc\FlushAllCommand as ApcFlushAllCommand;
use Sinergi\Core\Redis\FlushAllCommand as RedisFlushAllCommand;

class FlushAllCommand extends Command
{
    const RUN_VAGRANT = true;
    const COMMAND_NAME = 'flushall';

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

        (new ApcFlushAllCommand($this->getContainer()))->run($dummyInput, $dummyOutput);
        (new RedisFlushAllCommand($this->getContainer()))->run($dummyInput, $dummyOutput);

        $output->write('[ <fg=green>DONE</fg=green> ]', true);
    }
}
