<?php
namespace Sinergi\Core;

use Predis\Client;

class Predis
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if (null === $this->client) {
            $config = $this->registry->getConfig();
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
