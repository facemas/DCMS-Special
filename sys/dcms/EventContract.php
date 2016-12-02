<?php

namespace sys\dcms;

interface EventContract
{
    /**
     * Handle the event
     *
     * @return void
     */
    public  function handle();
}