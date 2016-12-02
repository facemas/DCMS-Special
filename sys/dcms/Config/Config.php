<?php

namespace sys\dcms\Config;

class Config
{
    use \sys\dcms\Traits\Singleton;
    /*
    private static $i;

    public static function make()
    {
        if (!static::$i) {
            static::$i = new static;
        }
        return static::$i;
    }*/

    public function get($file)
    {
        $path = sprintf('%s/config/%s.php',
                $_SERVER['DOCUMENT_ROOT'],
                $file
            );

        if (file_exists($path)) {
            $data = require_once $path;

            return $data;
        }
        return false;
    }
}