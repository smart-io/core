<?php
namespace Sinergi\Core;

use Cocur\Slugify\Slugify;
use Psr\Log\LoggerInterface;

class EmailLogger implements LoggerInterface
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var string
     */
    private $file;

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
     * @param string $message
     * @param array $context
     */
    private function logEmail($message, array $context = [])
    {
        if ($this->dir === null) {
            $this->dir = $this->registry->getConfig()->get('log.email_dir');
        }

        if (!is_dir($this->dir)) {
            mkdir($this->dir);
        }

        $slugify = new Slugify;

        $file = date('Y-m-d H-i-s ') .
            substr($slugify->slugify($context['toEmail']), 0, 28) . ' ' .
            substr($slugify->slugify($context['subject']), 0, 28);
        $file = $this->dir . DIRECTORY_SEPARATOR . $file;

        $originFile = $file;
        $count = 1;
        while (file_exists($file . '.txt')) {
            $file = $originFile . $count;
            $count++;
            if ($count >= 100) {
                break;
            }
        }

        $text = explode('<body', $message, 2);
        $text = isset($text[1]) ? $text[1] : '';
        if (!empty($text)) {
            $text = explode('>', $text, 2);
            $text = isset($text[1]) ? $text[1] : '';
            $text = trim(strip_tags($text));
            $lines = explode(PHP_EOL, $text);
            $text = '';
            $firstEmptyLine = true;
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $firstEmptyLine = true;
                    $text .= $line . PHP_EOL;
                } elseif ($firstEmptyLine) {
                    $firstEmptyLine = false;
                    $text .= PHP_EOL;
                }
            }
        }

        $body = "";
        $body .= "Date: " . date('Y-m-d H:i:s') . PHP_EOL;
        $body .= "From: {$context['fromName']} <{$context['fromEmail']}>" . PHP_EOL;
        $body .= "To: {$context['toName']} <{$context['toEmail']}>" . PHP_EOL;
        $body .= "Subject: {$context['subject']}" . PHP_EOL;
        $body .= PHP_EOL;
        $body .= "-------------------------------" . PHP_EOL;
        $body .= PHP_EOL;
        $body .= trim($text) . PHP_EOL;
        $body .= PHP_EOL;
        $body .= "-------------------------------" . PHP_EOL;
        $body .= PHP_EOL;
        $body .= trim($message);

        file_put_contents($file . '.txt', $body);
    }

    /**
     * @param string $level
     * @param string $message
     */
    private function writeLog($level, $message)
    {
        if (!empty($message)) {
            if ($this->file === null) {
                $this->file = $this->registry->getConfig()->get('log.email');
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
        $this->writeLog('Error', "Error sending email to \"{$context['toEmail']}\": {$message}");
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
        $this->writeLog('Info', "Sent email to \"{$context['toEmail']}\" with subject \"{$context['subject']}\"");
        $this->logEmail($message, $context);
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
    }
}
