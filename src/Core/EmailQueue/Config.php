<?php
namespace Sinergi\Core\EmailQueue;

use Smart\EmailQueue\ConfigInterface;
use Sinergi\Config\Config as SinergiConfig;

class Config implements ConfigInterface
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var SinergiConfig
     */
    private $config;

    /**
     * @param SinergiConfig $config
     */
    public function __construct(SinergiConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        if (null === $this->tableName) {
            $this->tableName = $this->config->get('doctrine.alias.email_queue');
        }
        return $this->tableName;
    }

    /**
     * @param string $tableName
     * @return $this
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }
}
