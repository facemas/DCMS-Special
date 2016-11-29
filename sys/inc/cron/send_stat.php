<?php
if ($dcms->send_stat_agree && !cache_events::get('send_stat')) {
    cache_events::set('send_stat', true, mt_rand(82800, 86400));
    stat::send();
}