<?php
namespace Sinergi\Core\Apc;

use Sinergi\Core\ContainerInterface;
use Sinergi\Core\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FlushAllCommand extends Command
{
    const RUN_VAGRANT = true;
    const COMMAND_NAME = 'apc:flushall';

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
            'Flush all APC cache'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Flushing all APC cache: ');
        // Try clearing all APC cache possible
        apc_clear_cache();
        apc_clear_cache('opcode');
        apc_clear_cache('user');
        // Restart the PHP-FPM service to clear web APC cache
        ob_start();
        passthru("sudo service php-fpm restart 2>&1");
        ob_end_clean();

        $output->write('[ <fg=green>DONE</fg=green> ]', true);
    }
}
