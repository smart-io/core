<?php
namespace Sinergi\Core;

class TestRuntime implements RuntimeInterface
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
        $this->detectTravis();
    }

    public function run()
    {
    }

    private function detectTravis()
    {
        $travis = getenv('TRAVIS');
        if ($travis) {
            $this->registry->getApp()->setEnv(App::ENV_TRAVIS);
            $this->registry->getConfig()->setEnvironment('travis');
        } elseif (is_array($_SERVER)) {
            foreach ($_SERVER as $key => $value) {
                if (stripos($key, 'TRAVIS') !== false) {
                    $this->registry->getApp()->setEnv(App::ENV_TRAVIS);
                    $this->registry->getConfig()->setEnvironment('travis');
                    break;
                }
            }
        }
    }
}
