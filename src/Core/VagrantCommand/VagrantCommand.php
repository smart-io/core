<?php
namespace Sinergi\Core\VagrantCommand;

use Sinergi\Core\Command;
use Sinergi\Core\RegistryInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VagrantCommand extends Command
{
    public static $isVagrant;

    /**
     * @param RegistryInterface $registry
     * @param SymfonyCommand $command
     */
    public function __construct(RegistryInterface $registry, SymfonyCommand $command)
    {
        $this->command = $command;
        $this->registry = $registry;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName($this->command->getName())
            ->setDescription($this->command->getDescription())
            ->setAliases($this->command->getAliases())
            ->setDefinition($this->command->getDefinition());
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->detectVagrant()) {
            $this->command->execute($input, $output);
        } else {
            $rootDir = $this->registry->getApp()->getRootDir();
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
            $command .= " " . $param;
        }
        return $command;
    }

    /**
     * @param $rootDir
     * @return null
     */
    public function getVagrantCwd($rootDir)
    {
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
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        return $this->registry;
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
