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
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->setConfig($config);
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
     * @return string
     */
    public function __toString()
    {
        return $this->language;
    }
}
