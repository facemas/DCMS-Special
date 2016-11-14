<?php

defined('SOCCMS') or die;
global $user, $dcms;

include H . '/forum/inc/functions.php';

$db = DB::me();

$today = mktime(0, 0, 0);
$yesterday = $today - 3600 * 24;

$cache_id = 'forum.last.posts_all';

if (false === ($themes_all = cache::get($cache_id))) {
    $themes_all = array();
    $q = $db->prepare("SELECT `th`.* , `tp`.`name` AS `topic_name`, `cat`.`name` AS `category_name`, `tp`.`group_write` AS `topic_group_write`, GREATEST(`th`.`group_show`, `tp`.`group_show`, `cat`.`group_show`) AS `group_show` FROM `forum_themes` AS `th` INNER JOIN `forum_topics` AS `tp` ON `tp`.`id` = `th`.`id_topic` AND `tp`.`theme_view` = :v JOIN `forum_categories` AS `cat` ON `cat`.`id` = `th`.`id_category` WHERE `th`.`time_last` > :t ORDER BY `th`.`time_last` DESC LIMIT 5");
    $q->execute(Array(':t' => TIME - 3600 * 24 * 7, ':v' => 1));

    $themes_all = $q->fetchAll();
}

$count = count($themes_all);
$themes_for_view = array();
for ($i = 0; $i < $count; $i++) {
    if ($themes_all[$i]['group_show'] > current_user::getInstance()->group) {
        continue;
    }
    $themes_for_view[] = $themes_all[$i];
}

$themes_ids = array();
$last_views = array(); // массив вида [id темы] => [дата последнего просмотра текущим пользователем]
$views_counters = array(); // счетчики просмотров тем
$new_messages = array(); // кол-во новых сообщений в теме за сегодня
$all_messages = array(); // общее кол-во сообщений в темах
$users_for_preload = array();
$count_themes = count($themes_for_view);

if ($count_themes) {
    for ($i = 0; $i < $count_themes; $i++) {
        $themes_ids[] = $themes_for_view[$i]['id'];
        $users_for_preload[] = $themes_for_view[$i]['id_autor'];
        $users_for_preload[] = $themes_for_view[$i]['id_last'];
    }
    $last_views = forum_getLastViewsTimes($themes_ids, current_user::getInstance()->id);
    $new_messages = forum_getMessagesCounters($themes_ids, NEW_TIME, current_user::getInstance()->group);
    $all_messages = forum_getMessagesCounters($themes_ids, 0, current_user::getInstance()->group);
    $views_counters = forum_getViewsCounters($themes_ids);
    new user($users_for_preload); // предзагрузка всех возможных пользователей одним SQL запросом
}

$pages = new pages($count_themes);
$start = $pages->my_start();
$end = $pages->end();

$listing = new listing();

$res = $db->query("SELECT COUNT(*) FROM `users_online` WHERE `request` LIKE '/forum/%'");
$users = $res->fetchColumn();

$post = $listing->post();
$post->title = __('Форум');
$post->highlight = true;
$post->icon('clipboard');
$post->url = '/forum/';
if ($users) {
    $post->counter = __('%s ' . misc::number($users, 'человек', 'человека', 'человек'), $users);
}

for ($z = $start; $z < $end && $z < $pages->posts; $z++) {
    $theme = $themes_for_view[$z];

    if (!isset($msg_today) && $theme['time_last'] >= $today) {
        $msg_today = true;
    }
    if (!isset($msg_yesterday) && $theme['time_last'] < $today && $theme['time_last'] >= $yesterday) {
        $msg_yesterday = true;
    }
    if (!isset($msg_week) && $theme['time_last'] < $yesterday) {
        $msg_week = true;
    }


    $post = $listing->post();

    $is_open = (int) ($theme['group_write'] <= $theme['topic_group_write']);

    $post->img = "/sys/images/icons/forum.theme.{$theme['top']}.$is_open.png";

    $post->time = misc::timek($theme['time_last']);
    $post->title = text::toValue($theme['name']);
    $post->url = '/forum/theme.php?id=' . $theme['id'] . '&amp;page=end';
    $autor = new user($theme['id_autor']);
    $last_msg = new user($theme['id_last']);
    $post->content .= ' <a class="btn btn-secondary btn-sm" style="float: right;"><i class="fa fa-comments-o fa-fw"></i> ' . (isset($new_messages[$theme['id']]) ? $all_messages[$theme['id']] . ' +' . $new_messages[$theme['id']] : $all_messages[$theme['id']]) . '</a>';
    $post->content .= ' <a class="btn btn-secondary btn-sm">' . ($autor->id != $last_msg->id ? $autor->nick . '/' . $last_msg->nick : $autor->nick) . '</a>';
    $post->content .= ' <a class="btn btn-secondary btn-sm"><i class="fa fa-eye fa-fw"></i> ' . $views_counters[$theme['id']] . '</a>';

}


$pages->display('?');
$listing->display(__('Нет новых тем'));
