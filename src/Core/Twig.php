<?php
namespace Sinergi\Core;

use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;
use Twig_Loader_String;

class Twig
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var Twig_Environment
     */
    private $environment;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
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
        $config = $this->registry->getConfig();

        if ($config->get('app.debug')) {
            $options['debug'] = true;
            $options['auto_reload'] = true;
        }

        if ($cache = $config->get('twig.cache')) {
            $options['cache'] = $cache;
        }

        $paths = $config->get('twig.paths');
        if (isset($paths)) {
            $loader = new Twig_Loader_Filesystem($paths);
        } else {
            $loader = new Twig_Loader_String();
        }
        $twig = new Twig_Environment($loader, $options);

        if (isset($options['debug']) && $options['debug']) {
            $twig->addExtension(new Twig_Extension_Debug());
        }

        return $twig;
    }
}
