<?php

namespace events;

use sys\dcms\AbstractEvent;

class TestEvent extends AbstractEvent
{
    public function handle()
    {
        echo 'This is test dcms event';
    }
}