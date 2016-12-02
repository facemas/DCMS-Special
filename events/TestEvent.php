<?php

namespace events;

use DB;
use sys\dcms\AbstractEvent;

class TestEvent extends AbstractEvent
{
    public function handle($data)
    {
        $stmt = DB::me()->prepare('insert from `chat_mini` (`id_user`,`message`,`time`)
            values (?,?,?)
        ');

        $stmt->execute([
            1,'Test events',time()
        ]);
    }
}