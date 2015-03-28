<?php

namespace Smart\Core;

use Sinergi\Container\ContainerInterface;
use Sinergi\Container\Twig\ContainerTwigExtension;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;
use Twig_Loader_String;

class Twig
{
    /**
     * @var Container
     */
    private $container;

    /**c
     * @var Twig_Environment
     */
    private $environment;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Twig_Environment
     */
    public function getEnvironment()
    {
        if (null === $this->environment) {
            $this->setEnvironment($this->createEnvironment());
        }
        return $this->environment;
    }

    /**
     * @param Twig_Environment $environment
     * @return $this
     */
    public function setEnvironment(Twig_Environment $environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @return Twig_Environment
     */
    protected function createEnvironment()
    {
        $options = [];
        $config = $this->container->getConfig();

        if ($config->get('app.debug')) {
            $options['debug'] = true;
            $options['auto_reload'] = true;
        }

        if ($cache = $config->get('twig.cache')) {
            $options['cache'] = $cache;
        }

        $paths = $config->get('twig.paths');
        if (isset($paths)) {
            $loader = new Twig_Loader_Filesystem();
            $firstPath = null;
            $hasMain = false;
            foreach ($paths as $namespace => $path) {
                if (null === $firstPath) {
                    $firstPath = $path;
                }
                if (is_int($namespace)) {
                    $hasMain = true;
                    $loader->addPath($path);
                } else {
                    $loader->addPath($path, $namespace);
                }
            }
            if (!$hasMain && null !== $firstPath) {
                $loader->addPath($firstPath);

            }
        } else {
            $loader = new Twig_Loader_String();
        }
        $twig = new Twig_Environment($loader, $options);

        if (isset($options['debug']) && $options['debug']) {
            $twig->addExtension(new Twig_Extension_Debug());
        }

        $twig->addExtension(new ContainerTwigExtension($this->container));

        return $twig;
    }
}
