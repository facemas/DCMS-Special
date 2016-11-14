<?php

defined('SOCCMS') or die;

$listing = new listing();
$post = $listing->post();
$post->highlight = true;
$post->icon('feed');
$post->url = '/news/';
$post->title = __('Все новости');

if ($dcms->widget_items_count) {
    $db = DB::me();
    $week = mktime(0, 0, 0, date('n'), -7);
    $q = $db->prepare("SELECT * FROM `news` WHERE `time` > ? ORDER BY `id` DESC LIMIT " . $dcms->widget_items_count);
    $q->execute(Array($week));
    while ($news = $q->fetch()) {
        $post = $listing->post();
        $post->icon('newspaper-o');
        $post->title = text::toValue($news['title']);
        $post->url = '/news/comments.php?id=' . $news['id'];
        $post->time = misc::timek($news['time']);
        //$post->highlight = $news['time'] > NEW_TIME;
    }
}

$listing->display();
