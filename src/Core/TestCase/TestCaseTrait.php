<?php
namespace Sinergi\Core\TestCase;

use Sinergi\Core\ContainerInterface;

trait TestCaseTrait
{
    /**
     * @return ContainerInterface
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
        /** @var \Sinergi\Core\App $app */
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
        return $this->rootDir;
    }

    /**
     * @param string $rootDir
     * @return $this
     */
    protected function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
        return $this;
    }

    /**
     * @return string
     */
    protected function getSrcDir()
    {
        return $this->srcDir;
    }

    /**
     * @param string $srcDir
     * @return $this
     */
    protected function setSrcDir($srcDir)
    {
        $this->srcDir = $srcDir;
        return $this;
    }

    /**
     * @return string
     */
    protected function getTestDir()
    {
        return $this->testDir;
    }

    /**
     * @param string $testDir
     * @return $this
     */
    protected function setTestDir($testDir)
    {
        $this->testDir = $testDir;
        return $this;
    }
}
