<?php
namespace Sinergi\Core;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class GearmanLogger implements LoggerInterface
{
    /**
     * @var string
     */
    private $logLevel;

    /**
     * @var string
     */
    private $file;

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
        $this->logLevel = $this->container->getConfig()->get('log.level');
    }

    /**
     * @param string $level
     * @param string $message
     */
    private function writeLog($level, $message)
    {
        if (!empty($message)) {
            if ($this->file === null) {
                $this->file = $this->container->getConfig()->get('log.gearman');
            }
            $content = date('Y-m-d H:i:s') . ' ' . $level . ': ' . $message . PHP_EOL;
            file_put_contents($this->file, $content, FILE_APPEND);
        }
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = [])
    {
        if (
            $this->logLevel === LogLevel::EMERGENCY ||
            $this->logLevel === LogLevel::ALERT ||
            $this->logLevel === LogLevel::CRITICAL ||
            $this->logLevel === LogLevel::ERROR ||
            $this->logLevel === LogLevel::WARNING ||
            $this->logLevel === LogLevel::NOTICE ||
            $this->logLevel === LogLevel::INFO ||
            $this->logLevel === LogLevel::DEBUG
        ) {
            $this->writeLog('Emergency', $message);
        }
    }

    /**
     * Action must be taken immediately.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = [])
    {
        if (
            $this->logLevel === LogLevel::ALERT ||
            $this->logLevel === LogLevel::CRITICAL ||
            $this->logLevel === LogLevel::ERROR ||
            $this->logLevel === LogLevel::WARNING ||
            $this->logLevel === LogLevel::NOTICE ||
            $this->logLevel === LogLevel::INFO ||
            $this->logLevel === LogLevel::DEBUG
        ) {
            $this->writeLog('Alert', $message);
        }
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = [])
    {
        if (
            $this->logLevel === LogLevel::CRITICAL ||
            $this->logLevel === LogLevel::ERROR ||
            $this->logLevel === LogLevel::WARNING ||
            $this->logLevel === LogLevel::NOTICE ||
            $this->logLevel === LogLevel::INFO ||
            $this->logLevel === LogLevel::DEBUG
        ) {
            $this->writeLog('Critical', $message);
        }
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = [])
    {
        if (
            $this->logLevel === LogLevel::ERROR ||
            $this->logLevel === LogLevel::WARNING ||
            $this->logLevel === LogLevel::NOTICE ||
            $this->logLevel === LogLevel::INFO ||
            $this->logLevel === LogLevel::DEBUG
        ) {
            $this->writeLog('Error', $message);
        }
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = [])
    {
        if (
            $this->logLevel === LogLevel::WARNING ||
            $this->logLevel === LogLevel::NOTICE ||
            $this->logLevel === LogLevel::INFO ||
            $this->logLevel === LogLevel::DEBUG
        ) {
            $this->writeLog('Warning', $message);
        }
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = [])
    {
        if (
            $this->logLevel === LogLevel::NOTICE ||
            $this->logLevel === LogLevel::INFO ||
            $this->logLevel === LogLevel::DEBUG
        ) {
            $this->writeLog('Notice', $message);
        }
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = [])
    {
        if (
            $this->logLevel === LogLevel::INFO ||
            $this->logLevel === LogLevel::DEBUG
        ) {
            $this->writeLog('Info', $message);
        }
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = [])
    {
        if (
            $this->logLevel === LogLevel::DEBUG
        ) {
            $this->writeLog('Debug', $message);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = [])
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
                $this->emergency($message, $context);
                break;
            case LogLevel::ALERT:
                $this->alert($message, $context);
                break;
            case LogLevel::CRITICAL:
                $this->critical($message, $context);
                break;
            case LogLevel::ERROR:
                $this->error($message, $context);
                break;
            case LogLevel::WARNING:
                $this->warning($message, $context);
                break;
            case LogLevel::NOTICE:
                $this->notice($message, $context);
                break;
            case LogLevel::INFO:
                $this->info($message, $context);
                break;
            case LogLevel::DEBUG:
                $this->debug($message, $context);
                break;
        }
    }
}
