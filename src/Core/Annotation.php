<?php
namespace Sinergi\Core;

use Doctrine\Common\Annotations\AnnotationRegistry;

class Annotation
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->setup();
    }

    public function setup()
    {
        AnnotationRegistry::registerLoader('class_exists');
        AnnotationRegistry::registerAutoloadNamespace(
            "Doctrine\\ORM\\Mapping",
            $this->registry->getApp()->getRootDir() .
            "/vendor/doctrine/orm/lib/Doctrine/ORM/Mapping"
        );
        AnnotationRegistry::registerAutoloadNamespace(
            "JMS\\Serializer\\Annotation",
            $this->registry->getApp()->getRootDir() .
            "/vendor/jms/serializer/src/JMS/Serializer/Annotation"
        );
    }
}
