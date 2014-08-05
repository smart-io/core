<?php
namespace Sinergi\Core\Test;

use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function __construct()
    {
        $this->initTestCaseTrait();
    }
}
