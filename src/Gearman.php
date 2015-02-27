<?php

namespace Smart\Core;

use Exception;
use Sinergi\Gearman\Application;
use Sinergi\Gearman\Config;
use Sinergi\Gearman\Dispatcher;
use Sinergi\Gearman\Process;

class Gearman
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        if (null === $this->config) {
            $this->setConfig($this->createConfig());
        }
        return $this->config;
    }

    /**
     * @return Config
     */
    private function createConfig()
    {
        $config = $this->container->getConfig();

        return (new Config())
            ->setBootstrap($this->container->getApp()->getRootDir() . '/bin/gearman.php')
            ->addServers($config->get('gearman.servers'))
            ->setUser($config->get('gearman.user'))
            ->setAutoUpdate($config->get('gearman.auto_update'));
    }

    /**
     * @param Dispatcher $dispatcher
     * @return $this
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        if (null === $this->dispatcher) {
            $this->setDispatcher(new Dispatcher($this->getConfig(), $this->container->getGearmanLogger()));
        }
        return $this->dispatcher;
    }

    /**
     * @param Application $application
     * @return $this
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        if (null === $this->application) {
            $this->setApplication(new Application($this->getConfig(), $this->getProcess(), null, $this->container->getGearmanLogger()));
        }
        try {
            $this->application->getWorker()->getWorker();
        } catch (\Exception $e) {
            null;
        }
        return $this->application;
    }

    /**
     * @param Process $process
     * @return $this
     */
    public function setProcess(Process $process)
    {
        $this->process = $process;
        return $this;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        if (null === $this->process) {
            $this->setProcess(new Process($this->getConfig(), $this->container->getGearmanLogger()));
        }
        return $this->process;
    }
}
