<?php

include_once '../sys/inc/start.php';
$doc = new document(3);
$doc->title = __('Удаление сообщений');

if (isset($_POST['delete'])) {
    $dcms->log('Чат', 'Очистка от всех сообщений');

    $db->query("TRUNCATE TABLE `chat_mini`");
    $doc->msg(__('Все сообщения успешно удалены'));
    $doc->toReturn('./');
    $doc->ret(__('Вернуться'), './');
    exit;
}

$form = new form('?' . passgen());
$form->bbcode('* ' . __('Все сообщения будут удалены без возможности восстановления'));
$form->button(__('Удалить'), "delete");
$form->display();

$doc->ret(__('Вернуться'), './');
