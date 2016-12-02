<?php

namespace sys\dcms;

use ArrayObject;
use sys\dcms\Config\Config;


class EventProvider
{
    use Traits\Singleton;
    /**
     * @var \ArrayObject
     */
    private $events;

    /**
     * @var array
     */
    private $log_events;

    /*

    private static $i;

    public static function make()
    {
        if (!static::$i) {
            static::$i = new static;
        }
        return static::$i;
    }*/

    protected function __construct()
    {
        $this->events = new ArrayObject();
    }
    


    public function registerEvent($event_key_code, array $params = [])
    {
        $config = Config::make()->get('events');

        var_dump($config);
        if (isset($config[$event_key_code])) {
            try {
                $this->run($config[$event_key_code]);

                var_dump($this->getLogs());
            }catch (\Exception $e) {
               throw $e;
            }
        }
    }

    private function run($events)
    {
        foreach ($events as $event)
        {
            $event = new \$event;
            $time_start = microtime(true);
            var_dump($event->handle());
            $this->logTime($time_start);
        }
    }

    private function logTime($start)
    {
        $this->log_events[] = microtime(true) - $start;
    }

    public function getLogs()
    {
        return $this->log_events;
    }

    public function getEvents()
    {
        return $this->events;
    }

}