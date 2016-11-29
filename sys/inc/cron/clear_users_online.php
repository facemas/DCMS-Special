<?php
if (!cache_events::get('clear_users_online')) {
    cache_events::set('clear_users_online', true, 30);
    $res = $db->prepare("DELETE FROM `users_online` WHERE `time_last` < ?");
    $res->execute(Array((TIME - SESSION_LIFE_TIME)));
}
