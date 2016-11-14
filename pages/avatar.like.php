<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Кому понравилось');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора аватара'));
    exit;
}
$id_st = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `users` WHERE `id` = ?");
$q->execute(Array($id_st));

if (!$ava = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Пользователь не найден'));
    exit;
}


$avtor = new user((int) $ava['id']);
if ($avtor->avatar == 0) {
    $doc->err(__('Аватар  не установлен'));
    exit;
}

$pages = new pages($db->query("SELECT COUNT(*) FROM `avatar_like` WHERE `id_avatar` = '" . $ava['id'] . "'")->fetchColumn());
$pages->this_page();
$listing = new listing();

$q = $db->query("SELECT * FROM `avatar_like` WHERE `id_avatar` = '" . $ava['id'] . "' ORDER BY `id` DESC LIMIT " . $pages->limit);
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $message) {
        $post = $listing->post();
        $ank = new user($message['id_user']);
        $post->url = '/profile.view.php?id=' . $ank->id;
        $post->time = misc::times($message['time']);
        $post->title = $ank->nick();
        $post->icon($ank->icon());
    }
}
$listing->display(__('Нет результатов'));
$pages->display('?id=' . $ava['id'] . '&amp;'); // вывод страниц

$doc->ret(__('Вернуться в анкету'), '/profile.view.php?id=' . $avtor->id);
