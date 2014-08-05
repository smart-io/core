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
        $commands = $this->registry->getDoctrine()->getCommands();
        $helperSet = $this->registry->getDoctrine()->getHelperSet();

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
