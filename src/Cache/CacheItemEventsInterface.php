<?php

namespace Smart\Core\Cache;

interface CacheItemEventsInterface
{
    public function onGet($object);
}
