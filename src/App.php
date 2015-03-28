<?php

namespace Smart\Core;

use Sinergi\Container\Application;
use Sinergi\Container\ApplicationInterface;

abstract class App extends Application implements ApplicationInterface
{
    /**
     * @return Container
     */
    abstract public function getContainer();

    const TEST_ENV = 'test';
    const DEV_ENV = 'dev';
    const PROD_ENV = 'prod';
    const LOCAL_ENV = 'local';
    const TRAVIS_ENV = 'travis';

    const COMMAND_RUNTIME = 'command';
    const DOCTRINE_RUNTIME = 'doctrine';
    const ROUTER_RUNTIME = 'router';
    const JOB_RUNTIME = 'job';
    const TEST_RUNTIME = 'test';

    const DEFAULT_CONFIG_DIRECTORY = 'config';

    /**
     * @var bool
     */
    protected $isConfigured = false;

    /**
     * @var string
     */
    protected $env = self::PROD_ENV;

    /**
     * @var string
     */
    protected $runtime = self::COMMAND_RUNTIME;

    /**
     * @var string
     */
    protected $configDir = self::DEFAULT_CONFIG_DIRECTORY;

    public function __construct()
    {
        $this->detectEnvironment();
        $this->configure();
    }

    /**
     * @return $this
     */
    protected function configure()
    {
        $config = $this->getContainer()->getConfig();
        $config->getPaths()->add($this->getRootDir() . DIRECTORY_SEPARATOR . $this->getConfigDir());
        $config->setEnvironment($this->getEnv());
        $this->isConfigured = true;
        return $this;
    }

    protected function detectEnvironment()
    {
        $env = getenv('APP_ENV');
        switch ($env) {
            case 'test':
            case 'tests':
            case 'testing':
                $this->setEnv(App::TEST_ENV);
                break;
            case 'development':
            case 'dev':
                $this->setEnv(App::DEV_ENV);
                break;
            case 'local':
                $this->setEnv(App::LOCAL_ENV);
                break;
            default:
                $this->setEnv(App::PROD_ENV);
                break;
        }
    }

    /**
     * @param null|string $runtime
     * @return $this
     */
    public function init($runtime = null)
    {
        if (null === $runtime) {
            $runtime = $this->getRuntime();
        } else {
            $this->setRuntime($runtime);
        }

        if ($runtime === self::ROUTER_RUNTIME) {
            $runtime = new RouterRuntime($this->getContainer());
        } elseif ($runtime === self::COMMAND_RUNTIME) {
            $runtime = new CommandRuntime($this->getContainer());
        } elseif ($runtime === self::DOCTRINE_RUNTIME) {
            $runtime = new DoctrineRuntime($this->getContainer());
        } elseif ($runtime === self::JOB_RUNTIME) {
            $runtime = new JobRuntime($this->getContainer());
        } elseif ($runtime === self::TEST_RUNTIME) {
            $runtime = new TestRuntime($this->getContainer());
        }

        if ($runtime) {
            $runtime->configure();
        }
        $this->run();
        if ($runtime) {
            $runtime->run();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getConfigDir()
    {
        return $this->configDir;
    }

    /**
     * @param string $configDir
     * @return $this
     */
    public function setConfigDir($configDir)
    {
        $this->configDir = $configDir;
        return $this;
    }
}
