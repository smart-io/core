<?php
namespace Sinergi\Core;

use Exception;
use Psr\Log\LoggerInterface;

class ErrorHandler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Error handler
     *
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     */
    public function error($errno, $errstr, $errfile, $errline)
    {
        $message = $errstr . ' in ' . $errfile . ' on line ' . $errline;
        if (null !== $this->logger) {
            switch ($errno) {
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_COMPILE_WARNING:
                    $this->logger->emergency($message);
                    break;
                case E_ERROR:
                case E_USER_ERROR:
                case E_PARSE:
                case E_RECOVERABLE_ERROR:
                    $this->logger->error($message);
                    break;
                case E_WARNING:
                case E_USER_WARNING:
                    $this->logger->warning($message);
                    break;
                case E_NOTICE:
                case E_USER_NOTICE:
                    $this->logger->notice($message);
                    break;
                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                case E_STRICT:
                    $this->logger->notice($message);
                    break;
                default:
                    $this->logger->error("Unknown error type: " . $errno . ": " . $message);
                    break;
            }
        }
    }

    /**
     * Uncatchable error handler
     */
    public function shutdown()
    {
        $error = error_get_last();

        if ($error !== NULL && $error["type"] !== E_CORE_WARNING && $error["type"] !== E_WARNING && $error["type"] !== E_USER_WARNING && $error["type"] !== E_NOTICE && $error["type"] !== E_USER_NOTICE) {
            if (null !== $this->logger) {
                $this->logger->emergency($error["message"] . ' in ' . $error["file"] . ' on line ' . $error["line"]);
            }
        }
    }

    /**
     * @param Exception $exception
     */
    public function exception($exception)
    {
        if (null !== $this->logger) {
            $this->logger->warning($exception->getMessage());
        }
    }
}
