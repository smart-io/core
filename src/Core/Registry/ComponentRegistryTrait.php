<?php
namespace Sinergi\Core\Registry;

use Doctrine\ORM\EntityManager;
use Klein\Klein;
use Klein\Request;
use Klein\Response;
use Psr\Log\LoggerInterface;
use Sinergi\Config\Config;
use Sinergi\Core\Doctrine;
use Sinergi\Core\EmailLogger;
use Sinergi\Core\ErrorLogger;
use Sinergi\Core\Gearman;
use Sinergi\Core\GearmanLogger;
use Sinergi\Core\Language;
use Sinergi\Core\Registry;
use Sinergi\Core\RegistryInterface;
use Sinergi\Core\Twig;
use Sinergi\Dictionary\Dictionary;
use Sinergi\Event\Dispatcher as EventDispatcher;
use Sinergi\Gearman\Dispatcher as GearmanDispatcher;
use Symfony\Component\Console\Application as ConsoleApplication;

trait ComponentRegistryTrait
{
    /**
     * @return RegistryInterface
     */
    abstract function getRegistry();

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->getRegistry()->get('request');
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->getRegistry()->set('request', $request);
        return $this;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->getRegistry()->get('response');
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->getRegistry()->set('response', $response);
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        if (null === $this->getRegistry()->get('config')) {
            $this->getRegistry()->set('config', new Config());
        }
        return $this->getRegistry()->get('config');
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->getRegistry()->set('config', $config);
        return $this;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        if (null === $this->getRegistry()->get('eventDispatcher')) {
            $this->getRegistry()->set('eventDispatcher', new EventDispatcher());
        }
        return $this->getRegistry()->get('eventDispatcher');
    }

    /**
     * @param EventDispatcher $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->getRegistry()->set('eventDispatcher', $eventDispatcher);
        return $this;
    }

    /**
     * @return Dictionary
     */
    public function getDictionary()
    {
        if (null === $this->getRegistry()->get('dictionary')) {
            $config = $this->getConfig()->get('dictionary');
            $dictionary = (new Dictionary())->setStorage($config['storage']);
            $dictionary->setLanguage((string)$this->getLanguage());
            $this->getRegistry()->set('dictionary', $dictionary);
        }
        return $this->getRegistry()->get('dictionary');
    }

    /**
     * @param Dictionary $dictionary
     * @return $this
     */
    public function setDictionary(Dictionary $dictionary)
    {
        $this->getRegistry()->set('dictionary', $dictionary);
        return $this;
    }

    /**
     * @return Twig
     */
    public function getTwig()
    {
        if (null === $this->getRegistry()->get('twig')) {
            $this->getRegistry()->set('twig', new Twig($this->getRegistry()));
        }
        return $this->getRegistry()->get('twig');
    }

    /**
     * @param Twig $twig
     * @return $this
     */
    public function setTwig(Twig $twig)
    {
        $this->getRegistry()->set('twig', $twig);
        return $this;
    }

    /**
     * @return Klein
     */
    public function getKlein()
    {
        if (null === $this->getRegistry()->get('klein')) {
            $this->getRegistry()->set('klein', new Klein());
        }
        return $this->getRegistry()->get('klein');
    }

    /**
     * @param Klein $klein
     * @return $this
     */
    public function setKlein(Klein $klein)
    {
        $this->getRegistry()->set('klein', $klein);
        return $this;
    }

    /**
     * @return ConsoleApplication
     */
    public function getConsoleApplication()
    {
        if (null === $this->getRegistry()->get('consoleApplication')) {
            $this->getRegistry()->set('consoleApplication', new ConsoleApplication());
        }
        return $this->getRegistry()->get('consoleApplication');
    }

    /**
     * @param ConsoleApplication $consoleApplication
     * @return $this
     */
    public function setConsoleApplication(ConsoleApplication $consoleApplication)
    {
        $this->getRegistry()->set('consoleApplication', $consoleApplication);
        return $this;
    }

    /**
     * @return Gearman
     */
    public function getGearman()
    {
        if (null === $this->getRegistry()->get('gearman')) {
            $this->getRegistry()->set('gearman', new Gearman($this->getRegistry()));
        }
        return $this->getRegistry()->get('gearman');
    }

    /**
     * @param Gearman $gearman
     * @return $this
     */
    public function setGearman(Gearman $gearman)
    {
        $this->getRegistry()->set('gearman', $gearman);
        return $this;
    }

    /**
     * @return GearmanDispatcher
     */
    public function getGearmanDispatcher()
    {
        return $this->getGearman()->getDispatcher();
    }

    /**
     * @return Doctrine
     */
    public function getDoctrine()
    {
        if (null === $this->getRegistry()->get('doctrine')) {
            $this->getRegistry()->set('doctrine', new Doctrine($this->getRegistry()));
        }
        return $this->getRegistry()->get('doctrine');
    }

    /**
     * @param Doctrine $doctrine
     * @return $this
     */
    public function setDoctrine(Doctrine $doctrine)
    {
        $this->getRegistry()->set('doctrine', $doctrine);
        return $this;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        if (null === $this->getRegistry()->get('language')) {
            $this->getRegistry()->set('language', new Language($this->getConfig()));
        }
        return $this->getRegistry()->get('language');
    }

    /**
     * @param Language $language
     * @return $this
     */
    public function setLanguage(Language $language)
    {
        $this->getRegistry()->set('language', $language);
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getEmailLogger()
    {
        if (null === $this->getRegistry()->get('emailLogger')) {
            $this->getRegistry()->set('emailLogger', new EmailLogger($this->getRegistry()));
        }
        return $this->getRegistry()->get('emailLogger');
    }

    /**
     * @param LoggerInterface $emailLogger
     * @return $this
     */
    public function setEmailLogger(LoggerInterface $emailLogger)
    {
        $this->getRegistry()->set('emailLogger', $emailLogger);
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getGearmanLogger()
    {
        if (null === $this->getRegistry()->get('gearmanLogger')) {
            $this->getRegistry()->set('gearmanLogger', new GearmanLogger($this->getRegistry()));
        }
        return $this->getRegistry()->get('gearmanLogger');
    }

    /**
     * @param LoggerInterface $gearmanLogger
     * @return $this
     */
    public function setGearmanLogger(LoggerInterface $gearmanLogger)
    {
        $this->getRegistry()->set('gearmanLogger', $gearmanLogger);
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getErrorLogger()
    {
        if (null === $this->getRegistry()->get('errorLogger')) {
            $this->getRegistry()->set('errorLogger', new ErrorLogger($this->getRegistry()));
        }
        return $this->getRegistry()->get('errorLogger');
    }

    /**
     * @param LoggerInterface $errorLogger
     * @return $this
     */
    public function setErrorLogger(LoggerInterface $errorLogger)
    {
        $this->getRegistry()->set('errorLogger', $errorLogger);
        return $this;
    }

    /**
     * @param null|string $name
     * @return EntityManager
     */
    public function getEntityManager($name = null)
    {
        return $this->getDoctrine()->getEntityManager($name);
    }
}
