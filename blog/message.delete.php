<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Удаление сообщения');

if (!isset($_GET ['id']) || !is_numeric($_GET ['id'])) {
    $doc->toReturn('./');
    $doc->err(__('Ошибка выбора сообщения'));
    exit();
}

$id_message = (int) $_GET['id'];
$id_blog = (int) $_GET['idblog'];

$q = $db->prepare("SELECT * FROM `blog_comment` WHERE `id` = ? LIMIT 1");
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

$autor = new user((int) $blogs['autor']);

if (isset($_GET['id']) && ($autor->id == $user->id || $user->group >= 2)) {

    $res = $db->prepare("DELETE FROM `blog_comment` WHERE `id` = ? LIMIT 1");
    $res->execute(Array($id_message));
    $res = $db->prepare("UPDATE `blog` SET `comm` = `comm`-1 WHERE `id` = ? LIMIT 1");
    $res->execute(array($id_blog));
    $doc->msg(__('Сообщение успешно удалено'));

    $ank = new user($message['id_user']);

    $dcms->log('Блоги', "Удаление сообщения от [url=/profile.view.php?id={$ank->id}]{$ank->login}[/url] ([when]$message[time][/when]):\n" . $message['mess']);

    $doc->toReturn('blog.php?blog=' . $blogs['id'] . '&amp;' . passgen() . '&');

    if (isset($_GET ['return'])) {
        $doc->ret(__('Вернуться'), text::toValue($_GET ['return']));
    } else {
        $doc->ret(__('Вернуться'), 'blog.php?blog=' . $blogs['id'] . '&amp;' . passgen() . '&');
    }
} else {
    $doc->msg(__('У вас нет доступа'));

    $doc->toReturn('blog.php?blog=' . $blogs['id'] . '&amp;' . passgen() . '&');
}