<?php
namespace Sinergi\Core;

use Sinergi\Config\Config;
use Sinergi\Registry\Application;
use Sinergi\Registry\ApplicationInterface;

abstract class App extends Application implements ApplicationInterface
{
    /**
     * @return RegistryInterface
     */
    abstract function getRegistry();

    const ENV_TEST = 'test';
    const ENV_DEV = 'dev';
    const ENV_PROD = 'prod';
    const ENV_LOCAL = 'local';
    const ENV_TRAVIS = 'travis';

    const RUNTIME_COMMAND = 'command';
    const RUNTIME_DOCTRINE = 'doctrine';
    const RUNTIME_ROUTER = 'router';
    const RUNTIME_GEARMAN = 'gearman';
    const RUNTIME_TEST = 'test';

    const DEFAULT_CONFIG_DIRECTORY = 'config';

    /**
     * @var bool
     */
    protected $isConfigured = false;

    /**
     * @var string
     */
    protected $env = self::ENV_PROD;

    /**
     * @var string
     */
    protected $runtime = self::RUNTIME_COMMAND;

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
        $config = $this->getRegistry()->getConfig();
        $config->setPath($this->getRootDir() . DIRECTORY_SEPARATOR . $this->getConfigDir());
        $config->setEnvironment($this->getEnv());
        date_default_timezone_set($config->get('app.timezone'));
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
                $this->setEnv(App::ENV_TEST);
                break;
            case 'development':
            case 'dev':
                $this->setEnv(App::ENV_DEV);
                break;
            case 'local':
                $this->setEnv(App::ENV_LOCAL);
                break;
            default:
                $this->setEnv(App::ENV_PROD);
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

        if ($runtime === self::RUNTIME_ROUTER) {
            $runtime = new RouterRuntime($this->getRegistry());
        } elseif ($runtime === self::RUNTIME_COMMAND) {
            $runtime = new CommandRuntime($this->getRegistry());
        } elseif ($runtime === self::RUNTIME_DOCTRINE) {
            $runtime = new DoctrineRuntime($this->getRegistry());
        } elseif ($runtime === self::RUNTIME_GEARMAN) {
            $runtime = new GearmanRuntime($this->getRegistry());
        } elseif ($runtime === self::RUNTIME_TEST) {
            $runtime = new TestRuntime($this->getRegistry());
        }

        $runtime->configure();
        $this->run();
        $runtime->run();

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
