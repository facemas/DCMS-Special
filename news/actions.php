<?php

include_once '../sys/inc/start.php';

$doc = new document(1);
$doc->title = __('Действия');

if (!isset($_GET['comment']) || !is_numeric($_GET['comment']) || !isset($_GET['id'])) {
    $doc->toReturn('./');
    $doc->err(__('Ошибка выбора сообщения'));
    exit();
}
$id_message = (int) $_GET['comment'];
$id_news = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `news_comments` WHERE `id` = '$id_message' LIMIT 1");
$q->execute(Array($id_message));
if (!$message = $q->fetch()) {
    $doc->toReturn('./');
    $doc->err(__('Сообщение не найдено'));
    exit();
}

$q = $db->prepare("SELECT * FROM `news` WHERE `id` = ? LIMIT 1");
$q->execute(Array($id_news));

if (!$news = $q->fetch()) {
    $doc->access_denied(__('Новость не найдена или удалена'));
}

$listing = new listing;

$ank = new user($message['id_user']);

$post = $listing->post();
$post->title = $ank->nick();
$post->content = text::toOutput($message['text']);
$post->time = misc::when($message['time']);
$post->image = $ank->getAvatar();

$post = $listing->post();
$post->title = __('Посмотреть анкету');
$post->icon('vcard-o');
$post->url = '/profile.view.php?id=' . $ank->id;

if ($user->group) {
    $post = $listing->post();
    $post->title = __('Ответить');
    $post->icon('pencil');
    $post->url = 'comments.php?id=' . $id_news . '&amp;com=' . $id_message . '&amp;reply';
    $post = $listing->post();
    $post->title = __('Цитировать');
    $post->icon('quote-left');
    $post->url = 'comments.php?id=' . $id_news . '&amp;com=' . $id_message . '&amp;quote';
}

if ($user->group >= 2) {
    $post = $listing->post();
    $post->title = __('Удалить сообщение');
    $post->icon('trash-o');
    $post->url = "comment.delete.php?id=$message[id]&amp;return=" . URL;
}
$listing->display();

$doc->ret(__('Вернуться в новость'), "comments.php?id=$id_news");
?>