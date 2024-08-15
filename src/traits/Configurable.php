<?php

namespace tauthz\traits;

trait Configurable
{
    /**
     * Gets config value by key.
     * 
     * @param string $key
     * @param string $default
     * 
     * @return mixed
     */
    protected function config(string $key = null, $default = null)
    {
        $driver = config('tauthz.default');
        return config('tauthz.enforcers.' . $driver . '.' . $key, $default);
    }
}
