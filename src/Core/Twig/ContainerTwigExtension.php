<?php
namespace Sinergi\Core\Twig;

use Sinergi\Core\ContainerInterface;
use Twig_Extension;

class ContainerTwigExtension extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        $globals = [];
        foreach (get_class_methods($this->container) as $method) {
            if (substr($method, 0, 3) === 'get') {
                $function = substr(strtolower($method), 3);
                $globals[$function] = new ContainerTwigGlobal($method, $this->container);
            }
        }
        return $globals;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'container.twig.extension';
    }
}
