<?php
namespace Sinergi\Core;

interface CacheableInterface
{
    public function exists($key);
    public function fetch($key);
    public function store($key, $value);
    public function delete($key);
}
