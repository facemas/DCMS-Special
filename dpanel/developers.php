<?php
include_once '../sys/inc/start.php';
$doc = new document(2);
$doc->title = __('Разработчики');
$doc->ret(__('Управление'), './');

$bb = new bb(H . '/sys/docs/developers.txt');
if ($bb->title) {
    $doc->title = $bb->title;
}

$listing = new listing();

$post = $listing->post();
$post->content[] = $bb->getText();
$listing->display();
