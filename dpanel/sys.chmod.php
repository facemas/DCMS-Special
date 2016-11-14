<?php

include_once '../sys/inc/start.php';
$doc = new document(5);
$doc->title = __('Проверка CHMOD');
$nw = ini::read(H . '/sys/ini/chmod.ini');

$listing = new listing();

$err = array();
foreach ($nw as $path) {
    $e = check_sys::getChmodErr($path, true);
    $post = $listing->post();
    $post->icon($e ? 'warning' : 'check-square-o');
    $post->title = $path;
    $err = array_merge($err, $e);
}

$listing->display();

if ($err) {
    $form = new form();
    $form->textarea('', '', implode("\r\n", $err));
    $form->bbcode('* ' . __('В зависимости от настроек на хостинге, CHMOD для возможности записи должен быть от 644 до 666'));
    $form->display();
} else
    $doc->msg(__('Необходимые права на запись имеются'));

$doc->ret(__('Управление'), '/dpanel/');
