<?php

namespace sys\dcms;

use ArrayObject;

class EventProvider
{
    /**
     * @var \ArrayObject
     */
    private $events;

    /**
     * @var array
     */
    private $log_events;

    public function __construct()
    {
        if ($this->self) {
            return $this->self;
        }

        $this->self = $this;
    }

    public function push(EventContract $event)
    {
        $this->events->append($event);

        return $this;
    }

    public function run()
    {
        foreach ($this->events as $event)
        {
            $event = new $event;

            if (method_exists($event, 'handle')) {
                $time_start = microtime(true);
                $event->handle();
                $this->logTime($time_start);
            }
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

}