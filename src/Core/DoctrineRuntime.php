<?php
namespace Sinergi\Core;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Symfony\Component\Console\Helper\HelperSet;

class DoctrineRuntime implements RuntimeInterface
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

    }

    public function run()
    {
        $entityManager = null;
        if (is_array($_SERVER['argv'])) {
            foreach ($_SERVER['argv'] as $key => $value) {
                if (substr($value, 0, 5) === '--em=') {
                    $entityManager = substr($value, 5);
                    unset($_SERVER['argv'][$key]);
                    if (is_int($_SERVER['argc'])) {
                        $_SERVER['argc']--;
                    }
                    break;
                }
            }
        }

        $commands = $this->registry->getDoctrine()->getCommands();
        $helperSet = $this->registry->getDoctrine()->getHelperSet($entityManager);

        if (!($helperSet instanceof HelperSet)) {
            foreach ($GLOBALS as $helperSetCandidate) {
                if ($helperSetCandidate instanceof HelperSet) {
                    $helperSet = $helperSetCandidate;
                    break;
                }
            }
        }

        ConsoleRunner::run($helperSet, $commands);
    }
}
