<?php

defined('SOCCMS') or die;
global $user, $dcms;
$db = DB::me();
$res = $db->prepare("SELECT COUNT(*) FROM `blog` WHERE `time_create` > ?");
$res->execute(Array(NEW_TIME));
$blog_c = $res->fetchColumn();

$listing = new listing();

$post = $listing->post();
$post->title = __('Блоги');
$post->highlight = true;
$post->icon('book');
$post->url = '/blog/';
if ($blog_c) {
    $post->counter = '+' . $blog_c;
}

if ($dcms->widget_items_count) {
    $q = $db->prepare("SELECT * FROM `blog` WHERE `time_create` > ? ORDER BY `id` DESC LIMIT " . $dcms->widget_items_count);
    $q->execute(Array(NEW_TIME));

    while ($blog = $q->fetch()) {
        $post = $listing->post();
        $post->icon('book');
        $post->title = text::toValue($blog['name']);
        $post->url = '/blog/blog.php?blog=' . $blog['id'];
        $post->time = misc::timek($blog['time_create']);
        $post->content = mb_substr(text::toValue($blog['message']), 0, 80, 'UTF-8');
        $post->bottom = "<i class='fa fa-comment-o fa-fw'></i> $blog[comm] <i class='fa fa-eye fa-fw'></i> $blog[view]";
    }
}
$listing->display();
