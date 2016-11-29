<?php

include_once '../sys/inc/start.php';
include 'inc/functions.php';
$doc = new document();

$doc->title = __('Форум');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора раздела'));

    exit;
}

$id_top = (int) $_GET['id'];

$q = $db->prepare("SELECT `forum_topics`.*, `forum_categories`.`name` AS `category_name` FROM `forum_topics` JOIN `forum_categories` ON `forum_categories`.`id` = `forum_topics`.`id_category` WHERE `forum_topics`.`id` = ? AND `forum_topics`.`group_show` <= ? AND `forum_categories`.`group_show` <= ?");
$q->execute(Array($id_top, $user->group, $user->group));
if (!$topic = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Раздел не доступен'));
    exit;
}

$doc->title .= ' - ' . $topic['name'];
$doc->description = $topic['description'];
$doc->keywords = $topic['keywords'];

$res = $db->prepare("SELECT COUNT(*) FROM `forum_themes` WHERE `id_topic` = ? AND `group_show` <= ?");
$res->execute(Array($topic['id'], $user->group));
$posts = array();
$pages = new pages;
$pages->posts = $res->fetchColumn();

$q = $db->prepare("SELECT `forum_themes`.* FROM `forum_themes` JOIN `forum_messages` ON `forum_messages`.`id_theme` = `forum_themes`.`id` WHERE `forum_themes`.`id_topic` = ? AND `forum_themes`.`group_show` <= ? AND `forum_messages`.`group_show` <= ? GROUP BY `forum_themes`.`id` ORDER BY `forum_themes`.`top`, `forum_themes`.`time_last` DESC LIMIT " . $pages->limit);
$q->execute(Array($topic['id'], $user->group, $user->group));

$listing = new listing();

if ($arr = $q->fetchAll()) {

    $themes_ids = array();
    foreach ($arr AS $theme) {
        $themes_ids[] = $theme['id'];
    }
    $themes_msg_counters = forum_getMessagesCounters($themes_ids, 0, current_user::getInstance()->group);
    $themes_views_counters = forum_getViewsCounters($themes_ids);

    foreach ($arr AS $theme) {
        $post = $listing->post();

        $is_open = (int) ($theme['group_write'] <= $topic['group_write']);

        $post->img = "/sys/images/icons/forum.theme.{$theme['top']}.$is_open.png";
        $post->title = text::toValue($theme['name']);
        $post->url = 'theme.php?id=' . $theme['id'];
        $post->time = misc::times($theme['time_last']);


        $autor = new user($theme['id_autor']);
        $last_msg = new user($theme['id_last']);
        $post->content .= ' <a class="btn btn-secondary btn-sm" style="float: right;"><i class="fa fa-comments-o fa-fw"></i> ' . $themes_msg_counters[$theme['id']] . '</a>';
        $post->content .= ' <a class="btn btn-secondary btn-sm">' . ($autor->id != $last_msg->id ? $autor->nick . '/' . $last_msg->nick : $autor->nick) . '</a>';
        $post->content .= ' <a class="btn btn-secondary btn-sm"><i class="fa fa-eye fa-fw"></i> ' . $themes_views_counters[$theme['id']] . '</a>';
    }
}


$listing->display(__('Доступных Вам тем нет'));

$pages->display('topic.php?id=' . $topic['id'] . '&amp;'); // вывод страниц

if ($topic['group_write'] <= $user->group) {
    $doc->opt(__('Новая тема'), 'theme.new.php?id_topic=' . $topic['id'] . "&amp;return=" . URL, false, '<i class="fa fa-plus fa-fw"></i>');
}

if ($topic['group_edit'] <= $user->group) {
    $doc->opt(__('Параметры раздела'), 'topic.edit.php?id=' . $topic['id'] . "&amp;return=" . URL, false, '<i class="fa fa-edit fa-fw"></i>');
}

$doc->ret($topic['category_name'], 'category.php?id=' . $topic['id_category']);
$doc->ret(__('Форум'), './');
