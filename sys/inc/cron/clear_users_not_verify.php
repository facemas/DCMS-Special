<?php
if ($dcms->clear_users_not_verify && !cache_events::get('clear_users_not_verify')) {
        cache_events::set('clear_users_not_verify', true, mt_rand(82800, 86400));
        $q = $db->prepare("SELECT `id` FROM `users` WHERE `a_code` <> '' AND `reg_date` < ?");
        $q->execute(Array((TIME - 86400)));
        if ($arr = $q->fetchAll()) {
            $count_delete = count($arr);
            foreach ($arr AS $u) {
                misc::user_delete($u['id']);
            }
            misc::log('Будет удалено неактивированных пользователей: '.$count_delete, 'system.users');
        }
    }

