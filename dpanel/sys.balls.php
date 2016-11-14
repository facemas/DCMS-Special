<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(5);
$doc->title = __('Управление баллами');

$balls = ini::read(H . '/sys/ini/sys.balls.ini');
if (isset($_POST['save'])) {
    foreach ($balls as $k => $v) {
        $dcms->$k = abs((int) $_POST[$k]);
    }
    $dcms->save_settings($doc);
}

$form = new form('?' . passgen());
foreach ($balls as $k => $v) {
    $form->text($k, __($v), $dcms->$k);
}
$form->button(__('Сохранить'), 'save');
$form->display();

$doc->ret(__('Управление'), '/dpanel/');
