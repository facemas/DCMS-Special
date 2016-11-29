<?php

include_once '../sys/inc/start.php';
$doc = new document(4);
$doc->title = __('Удаление новости');
$doc->ret(__('К новостям'), './');

$id_news = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `news` WHERE `id` = ? LIMIT 1");
$q->execute(Array($id_news));

if (!$news = $q->fetch()) {
    $doc->access_denied(__('Новость не найдена или уже удалена'));
}

$ank = new user($news['id_user']);

if ($ank->group > $user->group) {
    $doc->access_denied(__('У Вас нет прав для удаления данной новости'));
}

$doc->title .= ' "' . $news['title'] . '"';

if (isset($_POST['delete'])) {

    # Удаляем новость
    $res = $db->prepare("DELETE FROM `news` WHERE `id` = ? LIMIT 1");
    $res->execute(Array($news['id']));
    # Удаляем комментарии
    $res = $db->prepare("DELETE FROM `news_comments` WHERE `id_news` = ?");
    $res->execute(Array($news['id']));
    # Удаляем лайки к новости
    $res = $db->prepare("DELETE FROM `news_like` WHERE `id_news` = ?");
    $res->execute(Array($news['id']));
    # Удаляем опрос к новости если есть
    $res = $db->prepare("DELETE FROM `news_vote` WHERE `id_theme` = ?");
    $res->execute(Array($news['id']));
    # Удаляем голосовавших в этих опросах
    $res = $db->prepare("DELETE FROM `news_vote_votes` WHERE `id_theme` = ?");
    $res->execute(Array($news['id']));
    # Удаляем просмотры к новости
    $res = $db->prepare("DELETE FROM `news_views` WHERE `id_theme` = ?");
    $res->execute(Array($news['id']));

    $doc->msg(__('Новость успешно удалена'));
    header('Refresh: 1; url=./');
    exit;
}

$form = new form(new url());
$form->bbcode(__('Новость "%s" будет удалена без возможности восстановления', $news['title']));
$form->button(__('Удалить'), 'delete');
$form->display();
