<?php

namespace Smart\Core;

use Sinergi\Container\AbstractContainer;
use Sinergi\Container\ContainerInterface;

abstract class Container extends AbstractContainer implements ContainerInterface
{
    use ComponentContainerTrait;
}
