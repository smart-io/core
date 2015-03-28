<?php

namespace Smart\Core;

use Sinergi\Container\ContainerInterface;

class TestRuntime implements RuntimeInterface
{
    /**
     * @var Container
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
        $this->detectTravis();
    }

    public function run()
    {
    }

    private function detectTravis()
    {
        $travis = getenv('TRAVIS');
        if ($travis) {
            $this->container->getApp()->setEnv(App::TRAVIS_ENV);
            $this->container->getConfig()->setEnvironment('travis');
        } elseif (is_array($_SERVER)) {
            foreach ($_SERVER as $key => $value) {
                if (stripos($key, 'TRAVIS') !== false) {
                    $this->container->getApp()->setEnv(App::TRAVIS_ENV);
                    $this->container->getConfig()->setEnvironment('travis');
                    break;
                }
            }
        }
    }
}
