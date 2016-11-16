<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Действия');

if (!isset($_GET ['id']) || !is_numeric($_GET ['id']) || !isset($_GET ['idblog'])) {
    $doc->toReturn('./');
    $doc->err(__('Ошибка выбора сообщения'));
    exit();
}
$id_message = (int) $_GET['id'];
$id_blog = (int) $_GET['idblog'];
$q = $db->prepare("SELECT * FROM `blog_comment` WHERE `id` = '$id_message' LIMIT 1");
$q->execute(Array($id_message));
if (!$message = $q->fetch()) {
    $doc->toReturn('./');
    $doc->err(__('Сообщение не найдено'));
    exit();
}
$q = $db->prepare("SELECT `blog`.* , `blog_cat`.`name` AS `cat_name` FROM `blog` LEFT JOIN `blog_cat` ON `blog_cat`.`id` = `blog`.`id_cat` WHERE `blog`.`id` = ?");
$q->execute(Array($id_blog));

if (!$blogs = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Записи не существует'));
    exit;
}
$listing = new listing;
$ank = new user($message['id_user']);
$autor = new user((int) $blogs['autor']);

$post = $listing->post();
$post->title = $ank->nick();
$post->content = text::toOutput($message['mess']);
$post->time = misc::when($message['time']);
$post->icon($ank->icon());
$post = $listing->post();
$post->title = __('Посмотреть анкету');
$post->icon('vcard-o');
$post->url = '/profile.view.php?id=' . $ank->id;

if ($user->group) {
    $post = $listing->post();
    $post->title = __('Ответить');
    $post->icon('pencil');
    $post->url = 'blog.php?blog=' . $id_blog . '&amp;message=' . $id_message . '&amp;reply';
    $post = $listing->post();
    $post->title = __('Цитировать');
    $post->icon('quote-left');
    $post->url = 'blog.php?blog=' . $id_blog . '&amp;message=' . $id_message . '&amp;quote';
}

if ($autor->id == $user->id || $user->group >= 2) {
    $post = $listing->post();
    $post->title = __('Удалить сообщение');
    $post->icon('trash-o');
    $post->url = "message.delete.php?idblog=$id_blog&amp;id=$id_message";
}
$listing->display();

$doc->ret(__('Вернуться в блог'), "blog.php?blog=$id_blog");
?>