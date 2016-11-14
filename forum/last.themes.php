<?php

include_once '../sys/inc/start.php';
include 'inc/functions.php';
$doc = new document();
$doc->title = __('Новые темы');

$today = mktime(0, 0, 0);
$yesterday = $today - 3600 * 24;
$cache_id = 'forum.last.themes_all';

if (false === ($themes_all = cache::get($cache_id))) {
    $themes_all = array();
    $q = $db->prepare("SELECT `th`.* , `tp`.`name` AS `topic_name`, `cat`.`name` AS `category_name`, `tp`.`group_write` AS `topic_group_write`, GREATEST(`th`.`group_show`, `tp`.`group_show`, `cat`.`group_show`) AS `group_show` FROM `forum_themes` AS `th` INNER JOIN `forum_topics` AS `tp` ON `tp`.`id` = `th`.`id_topic` AND `tp`.`theme_view` = :v JOIN `forum_categories` AS `cat` ON `cat`.`id` = `th`.`id_category` WHERE `th`.`time_create` > :t ORDER BY `th`.`id` DESC");
    $q->execute(array(':t' => TIME - 3600 * 24 * 7, ':v' => 1)); // новые темы за неделю
    $themes_all = $q->fetchAll();

    cache::set($cache_id, $themes_all, 20);
}
$count = count($themes_all);
$themes_for_view = array(); // темы, которые может видеть текущий пользователь

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

for ($z = $start; $z < $end && $z < $pages->posts; $z++) {
    $theme = $themes_for_view[$z];

    if (!isset($msg_today) && $theme['time_create'] >= $today) {
        $post = $listing->post();
        $post->highlight = true;
        $post->title = __("Сегодня");
        $msg_today = true;
    }
    if (!isset($msg_yesterday) && $theme['time_create'] < $today && $theme['time_create'] >= $yesterday) {
        if ($listing->count()) {
            $listing->display();
            $listing = new listing();
        }

        $post = $listing->post();
        $post->highlight = true;
        $post->title = __("Вчера");
        $msg_yesterday = true;
    }
    if (!isset($msg_week) && $theme['time_create'] < $yesterday) {
        if ($listing->count()) {
            $listing->display();
            $listing = new listing();
        }

        $post = $listing->post();
        $post->highlight = true;
        $post->title = __("Неделя");
        $msg_week = true;
    }

    $post = $listing->post();

    if (current_user::getInstance()->id) {
        if (array_key_exists($theme['id'], $last_views)) {
            $post->highlight = $theme['time_last'] > $last_views[$theme['id']];
        } else {
            $post->highlight = true;
        }
    }

    $is_open = (int) ($theme['group_write'] <= $theme['topic_group_write']);

    $post->img = "/sys/images/icons/forum.theme.{$theme['top']}.$is_open.png";
    $post->time = misc::when($theme['time_create']);
    $post->title = text::toValue($theme['name']);

    $post->counter = $new_messages[$theme['id']] ? '+' . $new_messages[$theme['id']] : $all_messages[$theme['id']];
    $post->url = 'theme.php?id=' . $theme['id'] . '&amp;page=end';
    $autor = new user($theme['id_autor']);
    $last_msg = new user($theme['id_last']);
    $post->content = ($autor->id != $last_msg->id ? $autor->nick . '/' . $last_msg->nick : $autor->nick) . '<br />';
    $post->content .= "(<a href='category.php?id=$theme[id_category]'>" . text::toValue($theme['category_name']) . "</a> &gt; <a href='topic.php?id=$theme[id_topic]'>" . text::toValue($theme['topic_name']) . "</a>)<br />";
    $post->bottom = __('Просмотров: %s', $views_counters[$theme['id']]);

    if (!$doc->last_modified) {
        $doc->last_modified = $theme['time_last'];
    }
}

$listing->display(__('Тем не найдено'));
$pages->display('?');
$doc->ret(__('Форум'), './');
