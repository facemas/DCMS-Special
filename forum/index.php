<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Форум - Категории');

$listing = new listing();

$post = $listing->post();
$post->url = 'search.php';
$post->title = __('Поиск');
$post->icon('search');

if (false === ($new_themes = cache_counters::get('forum.new_themes.' . $user->group))) {
    $res = $db->prepare("SELECT COUNT(*) FROM `forum_themes` AS `th` INNER JOIN `forum_topics` AS `tp` ON `tp`.`id` = `th`.`id_topic` AND `tp`.`theme_view` = ? LEFT JOIN `forum_categories` AS `cat` ON `cat`.`id` = `th`.`id_category` WHERE `th`.`group_show` <= ? AND `tp`.`group_show` <= ? AND `cat`.`group_show` <= ? AND `th`.`time_create` > ?");
    $res->execute(Array(1, $user->group, $user->group, $user->group, NEW_TIME));
    $new_themes = $res->fetchColumn();
    cache_counters::set('forum.new_themes.' . $user->group, $new_themes, 20);
}

$post = $listing->post();
$post->url = 'last.themes.php';
$post->title = __('Новые темы');
if ($new_themes) {
    $post->counter = '+' . $new_themes;
}
$post->icon('file-text-o');

if (false === ($new_posts = cache_counters::get('forum.new_posts.' . $user->group))) {
    $res = $db->prepare("SELECT COUNT(DISTINCT(`msg`.`id_theme`)) FROM `forum_messages` AS `msg` LEFT JOIN `forum_themes` AS `th` ON `th`.`id` = `msg`.`id_theme` INNER JOIN `forum_topics` AS `tp` ON `tp`.`id` = `th`.`id_topic` AND `tp`.`theme_view` = ? LEFT JOIN `forum_categories` AS `cat` ON `cat`.`id` = `th`.`id_category` WHERE `th`.`group_show` <= ? AND `tp`.`group_show` <= ? AND `cat`.`group_show` <= ? AND `msg`.`group_show` <= ? AND `msg`.`time` > ?");
    $res->execute(Array(1, $user->group, $user->group, $user->group, $user->group, NEW_TIME));
    $new_posts = $res->fetchColumn();
    cache_counters::set('forum.new_posts.' . $user->group, $new_posts, 20);
}

$post = $listing->post();
$post->url = 'last.posts.php';
$post->title = __('Обновленные темы');
if ($new_posts) {
    $post->counter = '+' . $new_posts;
}
$post->icon('file-text-o');


if ($user->id) {
    if (false === ($my_themes = cache_counters::get('forum.my_themes.' . $user->id))) {
        $res = $db->prepare("SELECT COUNT(DISTINCT(`msg`.`id_theme`)) FROM `forum_messages` AS `msg` LEFT JOIN `forum_themes` AS `th` ON `th`.`id` = `msg`.`id_theme` LEFT JOIN `forum_topics` AS `tp` ON `tp`.`id` = `th`.`id_topic` LEFT JOIN `forum_categories` AS `cat` ON `cat`.`id` = `th`.`id_category` WHERE `th`.`id_autor` = ? AND `th`.`group_show` <= ? AND `tp`.`group_show` <= ? AND `cat`.`group_show` <= ? AND `msg`.`group_show` <= ? AND `msg`.`id_user` <> ? AND `msg`.`time` > ?");
        $res->execute(Array($user->id, $user->group, $user->group, $user->group, $user->group, $user->id, NEW_TIME));
        $my_themes = $res->fetchColumn();
        cache_counters::set('forum.my_themes.' . $user->id, $my_themes, 20);
    }


    $post = $listing->post();
    $post->url = 'my.themes.php';
    $post->title = __('Мои темы');
    if ($my_themes) {
        $post->counter = '+' . $my_themes;
    }
    $post->icon('list-alt');
}

$res = $db->prepare("SELECT COUNT(*) FROM `forum_categories` WHERE `group_show` <= ?");
$res->execute(Array($user->group));
$pages = new pages();
$pages->posts = $res->fetchColumn();

$q = $db->prepare("SELECT * FROM `forum_categories` WHERE `group_show` <= ? ORDER BY `position` ASC LIMIT " . $pages->limit);
$q->execute(Array($user->group));
while ($category = $q->fetch()) {
    $res = $db->prepare("SELECT COUNT(*) FROM `forum_topics` WHERE `id_category` = ? AND `group_show` <= ?");
    $res->execute(Array($category['id'], $user->group));
    $topicCount = $res->fetchColumn();

    $post = $listing->post();
    $post->url = "category.php?id=$category[id]";
    $post->title = text::toValue($category['name']);
    $post->icon('object-group');
    $post->post = text::for_opis($category['description']);
    $post->counter = $topicCount;
}

$listing->display(__('Доступных Вам категорий нет'));

$pages->display('?'); // вывод страниц

if ($user->group >= 5) {
    $doc->opt(__('Создать категорию'), 'category.new.php', false, '<i class="fa fa-plus fa-fw"></i>');
    $doc->opt(__('Порядок категорий'), 'categories.sort.php', false, '<i class="fa fa-list-ol fa-fw"></i>');
    $doc->opt(__('Статистика'), 'stat.php', false, '<i class="fa fa-pie-chart fa-fw"></i>');
}