<?php
namespace Sinergi\Core;

class EmailSender
{
    /**
     * @var string
     */
    protected $toName;

    /**
     * @var string
     */
    protected $toEmail;

    /**
     * @var string
     */
    protected $fromName;

    /**
     * @var string
     */
    protected $fromEmail;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * @param string $fromEmail
     * @return $this
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     * @return $this
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getToEmail()
    {
        return $this->toEmail;
    }

    /**
     * @param string $toEmail
     * @return $this
     */
    public function setToEmail($toEmail)
    {
        $this->toEmail = $toEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getToName()
    {
        return $this->toName;
    }

    /**
     * @param string $toName
     * @return $this
     */
    public function setToName($toName)
    {
        $this->toName = $toName;
        return $this;
    }

    /**
     * @return bool
     */
    public function send()
    {
        $logger = $this->registry->getEmailLogger();

        $headers = '';
        $headers .= "To: {$this->getToName()} <{$this->getToEmail()}>\r\n";
        $headers .= "From: {$this->getFromName()} <{$this->getFromEmail()}>\r\n";

        if (mail($this->getToEmail(), $this->getSubject(), $this->getBody(), $headers)) {
            $logger->info($this->getBody(), [
                'subject' => $this->getSubject(),
                'toName' => $this->getToName(),
                'toEmail' => $this->getToEmail(),
                'fromName' => $this->getFromName(),
                'fromEmail' => $this->getFromEmail()
            ]);
            return true;
        } else {
            $this->errorMessage = 'Error: Please use something better than PHP mail function to send emails';
            $logger->error($this->errorMessage, [
                'subject' => $this->getSubject(),
                'toName' => $this->getToName(),
                'toEmail' => $this->getToEmail(),
                'fromName' => $this->getFromName(),
                'fromEmail' => $this->getFromEmail()
            ]);
            return false;
        }
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->errorMessage;
    }
}
