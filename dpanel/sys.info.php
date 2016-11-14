<?php

include_once '../sys/inc/start.php';
$doc = new document(5);
$doc->title = __('Информация о системе');

$check = new check_sys();

$listing = new listing();

$post = $listing->post();
$post -> icon('scribd');
$post -> title = __('Версия DCMS Special: %s', $dcms->version);

foreach ($check->oks as $ok) {    
    $post = $listing->post();
    $post -> icon('check-square-o');
    $post -> title = $ok;
}
foreach ($check->notices as $note) {
    $post = $listing->post();
    $post -> icon('exclamation-circle');
    $post -> title = $note;
    $post -> highlight = true;
}
foreach ($check->errors as $err) {
    $post = $listing->post();
    $post -> icon('exclamation-triangle');
    $post -> title = $err;
    $post -> highlight = true;
}
$listing ->display();

$doc->ret(__('Управление'), '/dpanel/');