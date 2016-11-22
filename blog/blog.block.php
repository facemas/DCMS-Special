<?php

include_once '../sys/inc/start.php';
$doc = new document(2);
$doc->title = __('Блокировка записи');

if (!isset($_GET ['id']) || !is_numeric($_GET ['id'])) {
    if (isset($_GET ['return'])) {
        header('Refresh: 1; url=' . $_GET ['return']);
    } else {
        header('Refresh: 1; url=./');
    }
    $doc->err(__('Запись не выбрана'));
    exit();
}

$id_blog = (int) $_GET ['id'];

$q = $db->prepare("SELECT * FROM `blog` WHERE `id` = ?");
$q->execute(Array($id_blog));

if (!$blogs = $q->fetch()) {
    if (isset($_GET ['return'])) {
        header('Refresh: 1; url=' . $_GET ['return']);
    } else {
        header('Refresh: 1; url=./');
    }
    $doc->err(__('Записи не существует'));
    exit;
}

if (isset($_POST['off'])) {
    $a = (string) $_POST['prichina'];
    $a = text::input_text($a);
    $res = $db->prepare("UPDATE `blog` SET `prichina` = ?,`block`= ? WHERE `id`= ? LIMIT 1");
    $res->execute(Array($a, 1, $blogs['id']));
    $doc->msg(__('Запись успешно заблокирована'));

    header('Refresh: 1; url=blog.php?blog=' . $blogs ['id']);
}
if (isset($_POST['on'])) {
    $res = $db->prepare("UPDATE `blog` SET `prichina` = ?,`block`= ? WHERE `id`= ? LIMIT 1");
    $res->execute(Array(NULL, 0, $blogs['id']));
    $doc->msg(__('Запись успешно разблокирована'));

    header('Refresh: 1; url=blog.php?blog=' . $blogs ['id']);
}

if ($blogs['block'] == 0) {
    $form = new form('?id=' . $id_blog . '&amp;' . passgen());
    $form->textarea('prichina', __('Причина блокировки'));
    $form->button(__('Заблокировать'), 'off', false);
    $form->display();
} else {
    $form = new form('?id=' . $id_blog . '&amp;' . passgen());
    $form->button(__('Разблокировать'), 'on', false);
    $form->display();
}

$doc->ret(__('Блоги'), 'index.php');
