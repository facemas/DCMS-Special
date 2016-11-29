<?php

if ($log_of_visits && !cache_events::get('log_of_visits')) {
    cache_events::set('log_of_visits', true, mt_rand(82800, 86400));
    $log_of_visits->tally(); // подведение итогов
}

