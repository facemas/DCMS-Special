<?php
if ($dcms->update_auto && $dcms->update_auto_time && !cache_events::get('system.update.auto')) {
    cache_events::set('system.update.auto', true, $dcms->update_auto_time);
    include H.'/sys/inc/update.php';
    $mess = '';
    $update = new update();
    if (version_compare($update->version, $dcms->version, '>')) {

        if ($dcms->update_auto == 2 && @function_exists('ignore_user_abort') && @function_exists('set_time_limit')) {
            if ($update->start()) {
                // новая версия установлена
                $mess = __('Обновление DCMS Special (с %s по %s) успешно выполнено', $dcms->version, $update->version);
            } else {
                // при установке новой версии возникла ошибка
                $mess = __('При обновлении DCMS Special (с %s по %s) произошла ошибка', $dcms->version, $update->version).' '.__('Смотрите файл %s', 'system.update.log');
            }
        } else if ($dcms->update_auto_notified != $update->version) {
            $mess = __('Вышла новая версия DCMS Special: %s. [url=/dpanel/sys.update.php]Обновить[/url]', $update->version);
            $dcms->update_auto_notified = $update->version;
            $dcms->save_settings();
        }

        if ($mess) {
            $admins = groups::getAdmins();
            /** @var $admin user */
            foreach ($admins AS $admin) {
                $admin->mess($mess);
            }
        }
    }
    unset($mess, $update, $admins, $admin);
}