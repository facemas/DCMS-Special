<?php
if (!$cron_pseudo) {
    misc::log('Начало отправки писем из очереди', 'cron');
    mail::queue_process(true);
    misc::log('Очередь писем обработана', 'cron');
}
