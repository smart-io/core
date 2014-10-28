<?php
namespace Sinergi\Core\Cache;

interface CacheableEventsInterface
{
    public function onFetch($object);
}
