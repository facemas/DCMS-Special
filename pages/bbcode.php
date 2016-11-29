<?php

include_once '../sys/inc/start.php';

$doc = new document(1);
$doc->title = __('Список bbcode');

if (isset($_GET['info'])) {
    $faq = preg_replace('#[^a-z0-9_\-]+#ui', '', $_GET['info']);
    $bb = new bb(H . '/sys/docs/faq/' . $faq . '.txt');
    if ($bb->err) {
        $doc->toReturn('?');
        $doc->err(__('Запрошенная информация не найдена'));
        exit;
    }
}
if (isset($_GET['return'])) {
    $doc->ret(__('Вернуться'), urlencode($_GET['return']));
}

if (isset($_GET['info'])) {
    if ($bb->title) {
        $doc->title = $bb->title;
    }
    $bb->display();
    $doc->ret(__('Все bbcode'), '?');
} else {
    $menu = new menu_code('bbcode'); // загружаем меню 
    $menu->display();
}
$doc->ret(__('Личное меню'), '/menu.user.php');
