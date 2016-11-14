<?php
include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(6);
$doc->title = __('Отправка статистики');

if (isset($_POST)){
    $dcms->send_stat_agree = !empty($_POST['send_stat_agree']);
    $dcms->save_settings($doc);
}

$bb = new bb(H . '/sys/docs/send_stat.txt');

$form = new form('?' . passgen());
$form->bbcode($bb->getText());
$form->checkbox('send_stat_agree', __('Я разрешаю отправку статистики'), $dcms->send_stat_agree);
$form->button(__('Применить'));
$form->display();