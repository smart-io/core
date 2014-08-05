<?php
namespace Sinergi\Core;

use Sinergi\Core;

abstract class Email
{
    /**
     * @return RegistryInterface
     */
    abstract function getRegistry();

    /**
     * @param string $name
     * @param array $context
     * @return string
     */
    public function render($name, array $context = [])
    {
        return $this->getRegistry()->getTwig()->getEnvironment()->render($name, $context);
    }

    /**
     * @return bool
     */
    public function sendBackground()
    {
        return true;
    }
}
