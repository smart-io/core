<?php

namespace Smart\Core;

use Doctrine\Common\Annotations\AnnotationRegistry;

class Annotation
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->setup();
    }

    public function setup()
    {
        AnnotationRegistry::registerLoader('class_exists');
        AnnotationRegistry::registerAutoloadNamespace(
            "Doctrine\\ORM\\Mapping",
            $this->container->getApp()->getRootDir() .
            "/vendor/doctrine/orm/lib/Doctrine/ORM/Mapping"
        );
        AnnotationRegistry::registerAutoloadNamespace(
            "JMS\\Serializer\\Annotation",
            $this->container->getApp()->getRootDir() .
            "/vendor/jms/serializer/src/JMS/Serializer/Annotation"
        );
    }
}
