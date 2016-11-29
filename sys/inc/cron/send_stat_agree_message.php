<?php
if (!$dcms->send_stat_agree_message && !$dcms->send_stat_agree) {
    $dcms->send_stat_agree_message = true;
    $dcms->save_settings();

    $bb = new bb(H.'/sys/docs/send_stat.txt');
    $users = groups::getAdmins();
    /** @var $ank \user */
    foreach ($users AS $ank) {
        $ank->mess($bb->getText()."\n".'[url=/dpanel/sys.stat.php]'.__('Разрешить отправку статистики').'[/url]');
    }
    misc::log('Сообщение об отправке статистики отправлено', 'cron');
    unset($bb, $users, $ank);
}
