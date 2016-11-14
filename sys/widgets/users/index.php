<?php

defined('SOCCMS') or die;

global $user, $dcms;
$db = DB::me();
$res = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `a_code` = '' AND `reg_date` > ?");
$res->execute(Array(NEW_TIME));
$users = $res->fetchColumn();

$listing = new listing();

$post = $listing->post();
$post->highlight = true;
$post->icon('users');
$post->url = '/users.php';
$post->title = __('Последние зарегистрированные');
if ($users)
    $post->counter = '+' . $users;

if ($dcms->widget_items_count) {
    $q = $db->prepare("SELECT * FROM `users` WHERE `a_code` = '' AND `reg_date` > ? ORDER BY `id` DESC LIMIT " . $dcms->widget_items_count);
    $q->execute(Array(NEW_TIME));
    if ($arr = $q->fetchAll()) {
        foreach ($arr AS $ank) {
            $post = $listing->post();
            $p_user = new user($ank['id']);
            $post->icon($p_user->icon());
            $post->title = $p_user->nick();
            $post->url = '/profile.view.php?id=' . $p_user->id;
            $post->time = misc::times($p_user->reg_date);
        }
    }
}

$post = $listing->post();
$post->highlight = true;
$post->icon('users');
$post->title = __('Сейчас на сайте');
$post->url = '/online.users.php';
$res = $db->query("SELECT COUNT(*) FROM `users_online`");
$post->counter = $res->fetchColumn();

$post = $listing->post();
$post->highlight = true;
$post->icon('user-o');
$post->title = __('Гости на сайте');
$post->url = '/online.guest.php';
$res = $db->query("SELECT COUNT(*) FROM `guest_online` WHERE `conversions` >= '5' AND `is_robot` = '0'");
$post->counter = $res->fetchColumn();

$listing->display();