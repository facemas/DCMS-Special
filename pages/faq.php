<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Справка');
$faq = preg_replace('#[^a-z0-9_\-]+#ui', '', @$_GET['info']);
$bb = new bb(H . '/sys/docs/faq/' . $faq . '.txt');

if ($bb->err) {
    $doc->toReturn();
    $doc->err(__('Запрошенная информация не найдена'));
    exit;
}


if ($bb->title) {
    $doc->title = $bb->title;
}
$bb->display();


if (isset($_GET['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
}