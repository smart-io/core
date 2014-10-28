<?php
namespace Sinergi\Core\Cache;

interface CacheableInterface
{
    public function exists($key);
    public function fetch($key);
    public function store($key, $value);
    public function delete($key);
}
