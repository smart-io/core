<?php

namespace Smart\Core\TestCase;

use Smart\Core\Container;
use Sinergi\Container\ContainerInterface;

trait TestCaseTrait
{
    /**
     * @var bool
     */
    private $isTraitInitiated = false;

    /**
     * @return Container
     */
    protected $container;

    /**
     * @var string
     */
    protected $srcDir;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $testDir;

    protected function initTestCaseTrait()
    {
        $this->isTraitInitiated = true;
        /** @var \Smart\Core\App $app */
        global $app;
        $this->container = $app->getContainer();
        $this->setRootDir($app->getRootDir());
        $this->setSrcDir($app->getSrcDir());
        $this->setTestDir(realpath(preg_replace('#([\/|\\\\])src[\/|\\\\]#', '$1tests$1', $app->getSrcDir(), 1) . '/Tests'));
    }

    /**
     * @return string
     */
    protected function getRootDir()
    {
        if (!$this->isTraitInitiated) {
            $this->initTestCaseTrait();
        }
        return $this->rootDir;
    }

    /**
     * @param string $rootDir
     * @return $this
     */
    protected function setRootDir($rootDir)
    {
        if (!$this->isTraitInitiated) {
            $this->initTestCaseTrait();
        }
        $this->rootDir = $rootDir;
        return $this;
    }

    /**
     * @return string
     */
    protected function getSrcDir()
    {
        if (!$this->isTraitInitiated) {
            $this->initTestCaseTrait();
        }
        return $this->srcDir;
    }

    /**
     * @param string $srcDir
     * @return $this
     */
    protected function setSrcDir($srcDir)
    {
        if (!$this->isTraitInitiated) {
            $this->initTestCaseTrait();
        }
        $this->srcDir = $srcDir;
        return $this;
    }

    /**
     * @return string
     */
    protected function getTestDir()
    {
        if (!$this->isTraitInitiated) {
            $this->initTestCaseTrait();
        }
        return $this->testDir;
    }

    /**
     * @param string $testDir
     * @return $this
     */
    protected function setTestDir($testDir)
    {
        if (!$this->isTraitInitiated) {
            $this->initTestCaseTrait();
        }
        $this->testDir = $testDir;
        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        if (!$this->isTraitInitiated) {
            $this->initTestCaseTrait();
        }
        return $this->container;
    }
}
