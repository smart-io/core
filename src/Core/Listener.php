<?php
namespace Sinergi\Core;

use Sinergi\Core\Registry\ComponentRegistryTrait;
use Sinergi\Event\ListenerInterface;

abstract class Listener implements ListenerInterface
{
    use ComponentRegistryTrait;
}
