<?php
namespace Sinergi\Core\Cache;

interface CacheItemEventsInterface
{
    public function onGet($object);
}
