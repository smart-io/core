<?php

namespace Smart\Core;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Router\Request;
use Router\Response;
use Router\Router;
use Sinergi\Config\Config;
use Smart\Core\Doctrine\Doctrine;
use Sinergi\Dictionary\Dictionary;
use Sinergi\Gearman\Dispatcher as GearmanDispatcher;
use Symfony\Component\Console\Application as ConsoleApplication;
use Smart\Core\BrowserSession\BrowserSession;
use Sinergi\BrowserSession\BrowserSessionController;
use Sinergi\Container\ContainerInterface;
use Smart\Core\Predis\Predis;
use Smart\Core\Gearman\Gearman;
use Smart\Core\Language\Language;

trait ComponentContainerTrait
{
    /**
     * @return ContainerInterface|Container
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
            $this->getContainer()->set('language', new Language($this->getContainer()));
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

            $language = $this->getLanguage()->getLanguage();
            $dictionary->setLanguage($language);

            if (isset($config['extend'][$language])) {
                $dictionary->extend($config['extend'][$language]);
            }
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
    public function setTwig($twig)
    {
        $this->getContainer()->set('twig', $twig);
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
    public function getJobLogger()
    {
        if (!$jobLogger = $this->getContainer()->get('jobLogger')) {
            $jobLogger = new JobLogger($this->getContainer());
            $this->getContainer()->set('jobLogger', $jobLogger);
        }
        return $jobLogger;
    }

    /**
     * @param LoggerInterface $jobLogger
     * @return $this
     */
    public function setJobLogger(LoggerInterface $jobLogger)
    {
        $this->getContainer()->set('jobLogger', $jobLogger);
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

    /**
     * @return Router
     */
    public function getRouter()
    {
        if (!$router = $this->getContainer()->get('router')) {
            $router = new Router();
            $this->getContainer()->set('router', $router);
        }
        return $router;
    }

    /**
     * @param Router $router
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->getContainer()->set('router', $router);
        return $this;
    }

    /**
     * @return RouterApplication
     */
    public function getRouterApplication()
    {
        if (!$routerApplication = $this->getContainer()->get('routerApplication')) {
            $routerApplication = new RouterApplication($this->getContainer());
            $this->getContainer()->set('routerApplication', $routerApplication);
        }
        return $routerApplication;
    }

    /**
     * @param RouterApplication $routerApplication
     * @return $this
     */
    public function setRouterApplication(RouterApplication $routerApplication)
    {
        $this->getContainer()->set('routerApplication', $routerApplication);
        return $this;
    }

    /**
     * @return Request
     * @deprecated
     */
    public function getRequest()
    {
        if (!$request = $this->getContainer()->get('request')) {
            $request = Request::createFromGlobals();
            $this->getContainer()->set('request', $request);
        }
        return $request;
    }

    public function getPsrRequest()
    {
        return $this->getRouter()->getRequest();
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
        if (!$response = $this->getContainer()->get('response')) {
            $response = new Response;
            $this->getContainer()->set('response', $response);
        }
        return $response;
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
}
