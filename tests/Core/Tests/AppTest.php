<?php
namespace Sinergi\Core\Tests;

use PHPUnit_Framework_TestCase;

class AppTest extends PHPUnit_Framework_TestCase
{
    public function testDetectEnvironment()
    {
        putenv('APP_ENV=local');
        $app = new App();
        $this->assertEquals('local', $app->getEnv());

        putenv('APP_ENV=dev');
        $app = new App();
        $this->assertEquals('dev', $app->getEnv());

        putenv('APP_ENV=development');
        $app = new App();
        $this->assertEquals('dev', $app->getEnv());

        putenv('APP_ENV=somethingelse');
        $app = new App();
        $this->assertEquals('prod', $app->getEnv());

        putenv('APP_ENV=test');
        $app = new App();
        $this->assertEquals('test', $app->getEnv());
    }
}
