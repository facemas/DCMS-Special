<?php
if (!$dcms->donate_message && !cache_events::get('donate_message')) {
    cache_events::set('donate_message', true, mt_rand(82800, 86400));

    $bb = new bb(H.'/sys/docs/donate.txt');
    $sended = false;
    $month = mktime(0, 0, 0, date('n'), -30);
    $users = groups::getAdmins();
    /** @var $ank \user */
    foreach ($users AS $ank) {
        if ($ank->reg_date < $month) {
            $ank->mess($bb->getText());
            $sended = true;
        }
    }
    if ($sended) {
        $dcms->donate_message = TIME;
        $dcms->save_settings();

        misc::log('Сообщение о донате отправлено', 'cron');
    }
}
