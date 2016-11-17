<?php

defined('SOCCMS') or die;
$db = DB::me();

$listing = new ui_components();
$listing->ui_segment = true; //подключаем css segment
$listing->class = 'segments';

$res = $db->prepare("SELECT COUNT(*) FROM `news`");
$newsCount = $res->fetchColumn();

$post = $listing->post();
$post->list = true;
$post->class = 'ui secondary segment';
$post->icon('feed');
$post->url = '/news/';
$post->title = __('Все новости');
$post->counter = $db->query(" SELECT COUNT(*) FROM `news` ")->fetchColumn();


$week = mktime(0, 0, 0, date('n'), -7);
$q = $db->prepare("SELECT * FROM `news` WHERE `time` > ? ORDER BY `id` DESC LIMIT 1");
$q->execute(Array($week));
while ($news = $q->fetch()) {
    $post = $listing->post();
    $post->list = true;
    $post->class = 'ui segment';
    $post->icon('newspaper-o');
    $post->title = text::toValue($news['title']);
    $post->url = '/news/comments.php?id=' . $news['id'];
    $post->counter = misc::timek($news['time']);
}


$listing->display();
