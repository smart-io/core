<?php

namespace Smart\Core\Language;

use Sinergi\Container\ContainerInterface;
use Smart\Core\Container;

class Language
{
    /**
     * @var ContainerInterface|Container
     */
    private $container;

    /**
     * @var string
     */
    private $language;

    /**
     * @var array
     */
    private $languages;

    /**
     * @var string
     */
    private $defaultLanguage;

    /**
     * @param ContainerInterface|Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
        $this->setDefaultLanguage($this->getContainer()->getConfig()->get('language.default'));
        $this->setLanguage($this->getContainer()->getConfig()->get('language.default'));
        $languages = $this->getContainer()->getConfig()->get('language.languages');
        $this->setLanguages($languages);
    }

    /**
     * @param ContainerInterface|Container $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return ContainerInterface|Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * @return string
     */
    public function getDefaultShortLanguage()
    {
        return substr($this->defaultLanguage, 0, 2);
    }

    /**
     * @param string $defaultLanguage
     * @return $this
     */
    public function setDefaultLanguage($defaultLanguage)
    {
        $this->defaultLanguage = $defaultLanguage;
        return $this;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setLanguages(array $languages)
    {
        $this->languages = $languages;
        return $this;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @return array
     */
    public function getShortLanguages()
    {
        $array = $this->languages;
        foreach ($array as $key => $value) {
            $array[$key] = substr($value, 0, 2);
        }
        return $array;
    }

    /**
     * @return string
     */
    public function getShortCode()
    {
        return substr($this->language, 0, 2);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->language) {
            return substr($this->language, 0, 2);
        }
        return '';
    }
}
