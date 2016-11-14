<?php

include_once '../sys/inc/start.php';

$doc = new document(4);
$doc->title = __('Редактирование новости');
$doc->ret(__('Новости'), './');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора новости'));
    exit;
}

$id_news = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `news` WHERE `id` = ? LIMIT 1");
$q->execute(Array($id_news));

if (!$news = $q->fetch()) {
    $doc->access_denied(__('Новость не найдена или удалена'));
}

$ank = new user($news['id_user']);

if ($ank->group > $user->group) {
    $doc->access_denied(__('У Вас нет прав для редактирования данной новости'));
}

$news_edit = &$_SESSION['news_edit'][$id_news];

if (isset($_POST['clear'])) {
    $news_edit = array();
}

if (empty($news_edit)) {
    $news_edit = array();
    $news_edit['title'] = $news['title'];
    $news_edit['text'] = $news['text'];
    $news_edit['checked'] = false;
}

if ($news_edit['checked'] && isset($_POST['send'])) {
    $res = $db->prepare("UPDATE `news` SET `title` = ?, `id_user` = ?, `text` = ?, `sended` = '0' WHERE `id` = ? LIMIT 1");
    $res->execute(Array($news_edit['title'], $user->id, $news_edit['text'], $id_news));

    $dcms->log('Новости', 'Редактирование новости [url=/news/comments.php?id=' . $news['id'] . ']' . $news['title'] . '[/url]');

    $doc->msg(__('Изменения сохранены'));

    $news_edit = array();
    header('Refresh: 1; ./');
    exit;
}

if (isset($_POST['edit'])) {
    $news_edit['checked'] = 0;
}

if (isset($_POST['next'])) {
    $title = text::for_name($_POST['title']);
    $text = text::input_text($_POST['text']);

    if (!$title) {
        $doc->err(__('Заполните "Заголовок новости"'));
    } else {
        $news_edit['title'] = $title;
    }
    if (!$text) {
        $doc->err(__('Заполните "Текст новости"'));
    } else {
        $news_edit['text'] = $text;
    }

    if ($title && $text) {
        $news_edit['checked'] = 1;
    }
}


$form = new form(new url());
$form->text('title', __('Заголовок новости'), $news_edit['title'], true, false, $news_edit['checked']);
$form->textarea('text', __('Текст новости'), $news_edit['text'], true, $news_edit['checked']);

if ($news_edit['checked']) {
    $form->button(__('Редактировать'), 'edit', false);
    $form->button(__('Опубликовать изменения'), 'send', false);
} else {
    $form->button(__('Очистить'), 'clear', false);
    $form->button(__('Продолжить'), 'next', false);
}

$form->display();

$doc->ret(__('Вернуться в новость'), 'comments.php?id=' . $news['id']);
