<?php
namespace Sinergi\Core;

use Sinergi\Config\Config;

class Language
{
    /**
     * @var Config
     */
    private $config;

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
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->setConfig($config);
        $this->setDefaultLanguage($config->get('language.default'));
        $this->setLanguage($config->get('language.default'));
        $languages = $config->get('language.languages');
        $this->setLanguages($languages);
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
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
