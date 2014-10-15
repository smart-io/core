<?php
namespace Sinergi\Core\Registry;

use Doctrine\ORM\EntityManager;
use Sinergi\Core\Annotation;
use Sinergi\Core\Predis;
use Sinergi\Core\Serializer;
use Klein\Klein;
use Klein\Request;
use Klein\Response;
use Psr\Log\LoggerInterface;
use Sinergi\Config\Config;
use Sinergi\Core\Doctrine;
use Sinergi\Core\ErrorLogger;
use Sinergi\Core\Gearman;
use Sinergi\Core\GearmanLogger;
use Sinergi\Core\Language;
use Sinergi\Core\ContainerInterface;
use Sinergi\Core\Twig;
use Sinergi\Dictionary\Dictionary;
use Sinergi\Gearman\Dispatcher as GearmanDispatcher;
use Symfony\Component\Console\Application as ConsoleApplication;
use Sinergi\Core\BrowserSession\BrowserSession;
use Sinergi\BrowserSession\BrowserSessionController;

trait ComponentRegistryTrait
{
    /**
     * @return ContainerInterface
     */
    abstract function getContainer();

    /**
     * @return BrowserSessionController
     */
    public function getBrowserSessionController()
    {
        if (!$browserSession = $this->getContainer()->get('browserSession')) {
            $browserSession = new BrowserSession($this->getContainer());
            $this->getContainer()->set('browserSession', $browserSession);
        }
        return $browserSession->getController();
    }

    /**
     * @param BrowserSessionController $browserSessionController
     * @return $this
     */
    public function setBrowserSessionController(BrowserSessionController $browserSessionController)
    {
        if (!$browserSession = $this->getContainer()->get('browserSession')) {
            $browserSession = new BrowserSession($this->getContainer());
            $this->getContainer()->set('browserSession', $browserSession);
        }
        $browserSession->setController($browserSessionController);
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if (null === $this->getContainer()->get('request')) {
            $this->getContainer()->set('request', Request::createFromGlobals());
        }
        return $this->getContainer()->get('request');
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->getContainer()->set('request', $request);
        return $this;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        if (null === $this->getContainer()->get('response')) {
            $this->getContainer()->set('response', new Response());
        }
        return $this->getContainer()->get('response');
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->getContainer()->set('response', $response);
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        if (null === $this->getContainer()->get('config')) {
            $this->getContainer()->set('config', new Config());
        }
        return $this->getContainer()->get('config');
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->getContainer()->set('config', $config);
        return $this;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        if (!$this->getContainer()->get('language')) {
            $this->getContainer()->set('language', new Language($this->getContainer()->getConfig()));
        }
        return $this->getContainer()->get('language');
    }

    /**
     * @param Language $language
     * @return $this
     */
    public function setLanguage(Language $language)
    {
        $this->getContainer()->set('language', $language);
        return $this;
    }

    /**
     * @return Dictionary
     */
    public function getDictionary()
    {
        if (null === $this->getContainer()->get('dictionary')) {
            $config = $this->getConfig()->get('dictionary');
            $dictionary = (new Dictionary())->setStorage($config['storage']);
            $dictionary->setLanguage($this->getLanguage()->getLanguage());
            $this->getContainer()->set('dictionary', $dictionary);
        }
        return $this->getContainer()->get('dictionary');
    }

    /**
     * @param Dictionary $dictionary
     * @return $this
     */
    public function setDictionary(Dictionary $dictionary)
    {
        $this->getContainer()->set('dictionary', $dictionary);
        return $this;
    }

    /**
     * @return Twig
     */
    public function getTwig()
    {
        if (null === $this->getContainer()->get('twig')) {
            $this->getContainer()->set('twig', new Twig($this->getContainer()));
        }
        return $this->getContainer()->get('twig');
    }

    /**
     * @param Twig $twig
     * @return $this
     */
    public function setTwig(Twig $twig)
    {
        $this->getContainer()->set('twig', $twig);
        return $this;
    }

    /**
     * @return Klein
     */
    public function getKlein()
    {
        if (null === $this->getContainer()->get('klein')) {
            $this->getContainer()->set('klein', new Klein());
        }
        return $this->getContainer()->get('klein');
    }

    /**
     * @param Klein $klein
     * @return $this
     */
    public function setKlein(Klein $klein)
    {
        $this->getContainer()->set('klein', $klein);
        return $this;
    }

    /**
     * @return ConsoleApplication
     */
    public function getConsoleApplication()
    {
        if (null === $this->getContainer()->get('consoleApplication')) {
            $this->getContainer()->set('consoleApplication', new ConsoleApplication());
        }
        return $this->getContainer()->get('consoleApplication');
    }

    /**
     * @param ConsoleApplication $consoleApplication
     * @return $this
     */
    public function setConsoleApplication(ConsoleApplication $consoleApplication)
    {
        $this->getContainer()->set('consoleApplication', $consoleApplication);
        return $this;
    }

    /**
     * @return Gearman
     */
    public function getGearman()
    {
        if (null === $this->getContainer()->get('gearman')) {
            $this->getContainer()->set('gearman', new Gearman($this->getContainer()));
        }
        return $this->getContainer()->get('gearman');
    }

    /**
     * @param Gearman $gearman
     * @return $this
     */
    public function setGearman(Gearman $gearman)
    {
        $this->getContainer()->set('gearman', $gearman);
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
        if (null === $this->getContainer()->get('doctrine')) {
            $this->getContainer()->set('doctrine', new Doctrine($this->getContainer()));
        }
        return $this->getContainer()->get('doctrine');
    }

    /**
     * @param Doctrine $doctrine
     * @return $this
     */
    public function setDoctrine(Doctrine $doctrine)
    {
        $this->getContainer()->set('doctrine', $doctrine);
        return $this;
    }

    /**
     * @return Predis
     */
    public function getPredis()
    {
        if (null === $this->getContainer()->get('predis')) {
            $this->getContainer()->set('predis', new Predis($this->getContainer()));
        }
        return $this->getContainer()->get('predis');
    }

    /**
     * @param Predis $predis
     * @return $this
     */
    public function setPredis(Predis $predis)
    {
        $this->getContainer()->set('predis', $predis);
        return $this;
    }

    /**
     * @return Annotation
     */
    public function getAnnotation()
    {
        if (!$annotation = $this->getContainer()->get('annotation')) {
            $this->getContainer()->set('annotation', $annotation = new Annotation($this->getContainer()));
        }
        return $annotation;
    }

    /**
     * @param Annotation $annotation
     * @return $this
     */
    public function setAnnotation(Annotation $annotation)
    {
        $this->getContainer()->set('annotation', $annotation);
        return $this;
    }

    /**
     * @return Serializer
     */
    public function getSerializer()
    {
        if (!$serializer = $this->getContainer()->get('serializer')) {
            $this->getContainer()->set('serializer', $serializer = new Serializer($this->getContainer()));
        }
        return $serializer;
    }

    /**
     * @param Serializer $serializer
     * @return $this
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->getContainer()->set('serializer', $serializer);
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getGearmanLogger()
    {
        if (null === $this->getContainer()->get('gearmanLogger')) {
            $this->getContainer()->set('gearmanLogger', new GearmanLogger($this->getContainer()));
        }
        return $this->getContainer()->get('gearmanLogger');
    }

    /**
     * @param LoggerInterface $gearmanLogger
     * @return $this
     */
    public function setGearmanLogger(LoggerInterface $gearmanLogger)
    {
        $this->getContainer()->set('gearmanLogger', $gearmanLogger);
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getErrorLogger()
    {
        if (null === $this->getContainer()->get('errorLogger')) {
            $this->getContainer()->set('errorLogger', new ErrorLogger($this->getContainer()));
        }
        return $this->getContainer()->get('errorLogger');
    }

    /**
     * @param LoggerInterface $errorLogger
     * @return $this
     */
    public function setErrorLogger(LoggerInterface $errorLogger)
    {
        $this->getContainer()->set('errorLogger', $errorLogger);
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
