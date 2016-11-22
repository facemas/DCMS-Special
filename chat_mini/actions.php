<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Действия');

if (!isset($_GET ['id']) || !is_numeric($_GET ['id'])) {
    $doc->toReturn('./');
    $doc->err(__('Ошибка выбора сообщения'));
    exit();
}
$id_message = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `chat_mini` WHERE `id` = ? LIMIT 1");
$q->execute(Array($id_message));

if (!$message = $q->fetch()) {
    $doc->toReturn('./');
    $doc->err(__('Сообщение не найдено'));
    exit();
}

$listing = new listing;

$ank = new user($message['id_user']);

$post = $listing->post();
$post->title = $ank->nick();
$post->content = text::toOutput($message['message']);
$post->time = misc::when($message['time']);
$post->image = $ank->getAvatar();

$post = $listing->post();
$post->title = __('Посмотреть профиль');
$post->icon('vcard-o');
$post->url = '/profile.view.php?id=' . $ank->id;


if ($user->group) {
    $post = $listing->post();
    $post->title = __('Ответить');
    $post->icon('pencil');
    $post->url = 'index.php?message=' . $id_message . '&amp;reply';

    $post = $listing->post();
    $post->title = __('Цитировать');
    $post->icon('quote-left');
    $post->url = 'index.php?message=' . $id_message . '&amp;quote';
}

if ($user->group >= 2) {
    $post = $listing->post();
    $post->title = __('Удалить сообщение');
    $post->icon('trash-o');
    $post->url = 'message.delete.php?id=' . $id_message;
}

$listing->display();

$doc->ret(__('Вернуться'), './');

