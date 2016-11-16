<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Кому понравилась запись');

if (!isset($_GET ['id']) || !is_numeric($_GET ['id'])) {
    if (isset($_GET ['return']))
        header('Refresh: 1; url=' . $_GET ['return']);
    else
        header('Refresh: 1; url=./');
    $doc->err(__('Запись не выбрана'));
    exit();
}
$id_blog = (int) $_GET ['id'];
$q = $db->prepare("SELECT * FROM `blog` WHERE `id` = ?");
$q->execute(Array($id_blog));
if (!$blogs = $q->fetch()) {
    if (isset($_GET ['return']))
        header('Refresh: 1; url=' . $_GET ['return']);
    else
        header('Refresh: 1; url=./');
    $doc->err(__('Записи не существует'));
    exit;
}


$pages = new pages($db->query("SELECT COUNT(*) FROM `blog_like` WHERE `id_blog` = '" . $blogs['id'] . "'")->fetchColumn());
$pages->this_page();
$listing = new listing();

$q = $db->query("SELECT * FROM `blog_like` WHERE `id_blog` = '" . $blogs['id'] . "' ORDER BY `id` DESC LIMIT " . $pages->limit);
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $message) {
        $post = $listing->post();
        $ank = new user($message['id_user']);
        $post->url = '/profile.view.php?id=' . $ank->id;
        $post->time = misc::when($message['time']);
        $post->title = $ank->nick();
        $post->icon($ank->icon());
    }
}
$listing->display(__('Еще ни кому не понравилось'));
$pages->display('?blog=' . $blogs['id'] . '&amp;'); // вывод страниц

$doc->ret(__('Вернуться к записи'), '/blog/blog.php?blog=' . $blogs['id']);
