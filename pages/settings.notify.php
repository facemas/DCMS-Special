<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Настройки уведомлений');

if (isset($_POST ['save'])) {
    $user->notice_mention = !empty($_POST ['notice_mention']);
    $user->notification_forum = !empty($_POST ['notification_forum']);
    $doc->msg(__('Параметры успешно сохранены'));
}

$form = new form('?' . passgen());
$form->checkbox('notice_mention', __('Упоминание ника (@%s)', $user->login), $user->notice_mention);
$form->checkbox('notification_forum', __('Ответ на форуме'), $user->notification_forum);
$form->button(__('Сохранить'), 'save');
$form->display();

$doc->ret(__('Личное меню'), '/menu.user.php');
