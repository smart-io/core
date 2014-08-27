<?php
namespace Sinergi\Core;

use Twig_Extension;

class RegistryTwigExtension extends Twig_Extension
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

    /**
     * @return array
     */
    public function getGlobals()
    {
        $globals = [];
        foreach (get_class_methods($this->registry) as $method) {
            if (substr($method, 0, 3) === 'get') {
                $function = substr(strtolower($method), 3);
                $globals[$function] = new RegistryTwigGlobal($method, $this->registry);
            }
        }
        return $globals;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'registry.twig.extension';
    }
}
