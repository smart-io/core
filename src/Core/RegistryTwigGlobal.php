<?php
namespace Sinergi\Core;

class RegistryTwigGlobal
{
    /**
     * @var string
     */
    private $service;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param string $service
     * @param RegistryInterface $registry
     */
    public function __construct($service, RegistryInterface $registry)
    {
        $this->service = $service;
        $this->registry = $registry;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        $service = call_user_func([$this->registry, $this->service]);
        return call_user_func_array([$service, $name], $arguments);
    }

    public function __toString()
    {
        $object = call_user_func([$this->registry, $this->service]);
        if (is_object($object)) {
            return (string)$object;
        }
        return null;
    }
}
