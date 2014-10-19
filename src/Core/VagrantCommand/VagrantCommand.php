<?php
namespace Sinergi\Core\VagrantCommand;

use Sinergi\Core\Command;
use Sinergi\Core\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VagrantCommand extends Command
{
    const VAGRANT_PATH_COMMAND = 'vagrant-path';

    /**
     * @var boolean
     */
    public static $isVagrant;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @param ContainerInterface $container
     * @param SymfonyCommand $command
     */
    public function __construct(ContainerInterface $container, SymfonyCommand $command)
    {
        $this->command = $command;
        $this->container = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName($this->command->getName())
            ->setDescription($this->command->getDescription())
            ->setAliases($this->command->getAliases())
            ->setDefinition($this->command->getDefinition());
        $this->addOption(self::VAGRANT_PATH_COMMAND, null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        if ($this->detectVagrant()) {
            $this->command->execute($input, $output);
        } else {
            $rootDir = $this->getContainer()->getApp()->getRootDir();
            chdir($rootDir);
            $command = $this->getVagrantCommand($rootDir);
            ob_start();
            passthru("vagrant ssh -c \"" . $command . "\" 2>&1");
            $retval = ob_get_contents();
            ob_end_clean();
            $output->write($retval);
        }
    }

    /**
     * @param $rootDir
     * @return string
     */
    public function getVagrantCommand($rootDir)
    {
        $file = str_replace($rootDir, '', $_SERVER['SCRIPT_NAME']);
        $command = "sudo php " . $this->getVagrantCwd($rootDir) . $file;
        foreach (array_splice($_SERVER['argv'], 1) as $param) {
            if (substr($param, 0, strlen('--' . self::VAGRANT_PATH_COMMAND)) !== '--' . self::VAGRANT_PATH_COMMAND) {
                $command .= " " . $param;
            }
        }
        return $command;
    }

    /**
     * @param $rootDir
     * @return null
     */
    public function getVagrantCwd($rootDir)
    {
        $path = $this->input->getOption('vagrant-path');
        if ($path) {
            return $path;
        }

        $path = $this->getContainer()->getConfig()->get('vagrant.path');
        if ($path) {
            return $path;
        }

        $file = $rootDir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, [
                ".vagrant",
                "machines",
                "default",
                "virtualbox",
                "synced_folders",
            ]);
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if ($content) {
                $content = json_decode($content, true);
                if (is_array($content)) {
                    foreach (current($content) as $folder) {
                        if ($folder['hostpath'] === $rootDir) {
                            return $folder['guestpath'];
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return bool
     */
    public function detectVagrant()
    {
        if (null !== self::$isVagrant) {
            return self::$isVagrant;
        }

        ob_start();
        passthru("id vagrant 2>&1");
        $retval = ob_get_contents();
        ob_end_clean();
        if (stripos($retval, "uid=") !== false) {
            return self::$isVagrant = true;
        }
        return self::$isVagrant = false;
    }
}
