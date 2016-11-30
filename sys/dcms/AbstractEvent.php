<?php

namespace sys\dcms;

abstract class AbstractEvent implements EventContract
{
    /**
     * @inheritdoc
     */
    public abstract function handle();
}