<?php

namespace Smart\Core;

use Sinergi\Container\ContainerInterface;

class Serializer
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
