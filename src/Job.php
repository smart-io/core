<?php

namespace Smart\Core;

use Sinergi\Gearman\JobInterface;

abstract class Job implements JobInterface
{
    use ComponentRegistryTrait;
}
