<?php

namespace sys\dcms\Traits;

trait Singleton
{
    private static $i = false;

    public static function make()
    {
        if (!static::$i) {
            static::$i = new static;
        }
        return static::$i;
    }
}
