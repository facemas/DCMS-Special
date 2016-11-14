<?php

include_once '../sys/inc/start.php';
$doc = new document ();
$doc->title = __('Логины');

$ank = (empty($_GET['id'])) ? $user : new user((int) $_GET['id']);

if (!$ank->group) {
    $doc->access_denied(__('Нет данных'));
}

$doc->title = ($user->id && $ank->id == $user->id) ? __('Мои логины') : __('Логины "%s"', $ank->nick);

$doc->description = __('Логины "%s"', $ank->nick);
$doc->keywords [] = $ank->login;

$pages = new pages($db->query("SELECT COUNT(*) FROM `login_history` WHERE `id_user` = '$ank->id'"));
$listing = new listing();

$q = $db->query("SELECT * FROM `login_history` WHERE `id_user` = '$ank->id' ORDER BY `time` DESC LIMIT " . $pages->limit);
$res = $q->fetchAll();

foreach ($res AS $login) {
    $post = $listing->post();
    $post->title = $login['login'];
    $post->time = misc::when($login['time']);
}

$listing->display(__('Изменений логинов не обнаружено'));
$pages->display('?');

$doc->ret(__('Анкета'), '/profile.view.php?id=' . $ank->id);

if ($user->group) {
    $doc->ret(__('Личное меню'), '/menu.user.php');
}

