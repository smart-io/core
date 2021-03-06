<?php

namespace Smart\Core\Predis;

use Predis\Client;
use Sinergi\Container\ContainerInterface;
use Smart\Core\Container;

class Predis
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if (null === $this->client) {
            $config = $this->container->getConfig();
            $this->client = new Client([
                'scheme' => $config->get('predis.scheme'),
                'host' => $config->get('predis.host'),
                'port' => $config->get('predis.port'),
            ]);
        }
        return $this->client;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }
}
