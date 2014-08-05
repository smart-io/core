<?php
namespace Sinergi\Core;

use Sinergi\Core\Registry\ComponentRegistryTrait;
use Sinergi\Gearman\JobInterface;

abstract class Job implements JobInterface
{
    use ComponentRegistryTrait;
}
