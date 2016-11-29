<?php

if (defined('SOCCMS')) {
    $cron_pseudo = true;
} else {
    $cron_pseudo = false;
    require_once dirname(__FILE__) . '/inc/start.php';
}

function execute_cron_file($path) {
    global $db, $dcms, $log_of_visits;
    require $path;
}

if (!cache_events::get('cron')) {
    cache_events::set('cron', TIME, 10);

    misc::log('CRON start', 'cron');

    $cron_files = (array) @glob(H . '/sys/inc/cron/*.php');
    foreach ($cron_files as $path) {
        $name = basename($path, '.php');
        misc::log('start - ' . $name, 'cron');
        execute_cron_file($path);
        misc::log('end - ' . $name, 'cron');
        misc::log('-----------------------' . "\r\n", 'cron');
    }

    misc::log('CRON finish' . "\r\n" . "\r\n", 'cron');
}
