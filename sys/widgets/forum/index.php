<?php

defined('SOCCMS') or die;
global $user;
$db = DB::me();
if (false === ($new_posts = cache_counters::get('forum.new_posts.' . $user->group))) {
    $res = $db->prepare("SELECT COUNT(*) FROM `forum_themes` AS `th` INNER JOIN `forum_topics` AS `tp` ON `tp`.`id` = `th`.`id_topic` AND `tp`.`theme_view` = :v LEFT JOIN `forum_categories` AS `cat` ON `cat`.`id` = `th`.`id_category` WHERE `th`.`group_show` <= :g AND `tp`.`group_show` <= :g AND `cat`.`group_show` <= :g AND `th`.`time_last` > :t");
    $res->execute(Array(':v' => 1, ':g' => $user->group, ':t' => NEW_TIME));
    $new_posts = $res->fetchColumn();
    cache_counters::set('forum.new_posts.' . $user->group, $new_posts, 60);
}


if (false === ($new_themes = cache_counters::get('forum.new_themes.' . $user->group))) {
    $res = $db->prepare("SELECT COUNT(*) FROM `forum_themes` AS `th` INNER JOIN `forum_topics` AS `tp` ON `tp`.`id` = `th`.`id_topic` AND `tp`.`theme_view` = :v LEFT JOIN `forum_categories` AS `cat` ON `cat`.`id` = `th`.`id_category` WHERE `th`.`group_show` <= :g AND `tp`.`group_show` <= :g AND `cat`.`group_show` <= :g AND `th`.`time_create` > :t");
    $res->execute(Array(':v' => 1, ':g' => $user->group, ':t' => NEW_TIME));
    $new_themes = $res->fetchColumn();
    cache_counters::set('forum.new_themes.' . $user->group, $new_themes, 60);
}

$res = $db->query("SELECT COUNT(*) FROM `users_online` WHERE `request` LIKE '/forum/%'");
$users = $res->fetchColumn();

$listing = new listing();

$post = $listing->post();
$post->highlight = true;
$post->icon('clipboard');
$post->url = '/forum/';
$post->title = __('Форум');
if ($users) {
    $post->counter = __('%s ' . misc::number($users, 'человек', 'человека', 'человек'), $users);
}


$post = $listing->post();
$post->icon('file-text-o');
$post->url = '/forum/last.posts.php';
$post->title = __('Темы с новыми сообщениями');
if ($new_posts)
    $post->counter = '+' . $new_posts;

$post = $listing->post();
$post->icon('clipboard');
$post->url = '/forum/last.themes.php';
$post->title = __('Новые темы');
if ($new_themes)
    $post->counter = '+' . $new_themes;

$listing->display();
