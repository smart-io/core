<?php

namespace Smart\Core\Doctrine;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Smart\Core\ContainerInterface;
use Smart\Core\RuntimeInterface;
use Symfony\Component\Console\Helper\HelperSet;

class DoctrineRuntime implements RuntimeInterface
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

        $commands = $this->container->getDoctrine()->getCommands();
        $helperSet = $this->container->getDoctrine()->getHelperSet($entityManager);

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
