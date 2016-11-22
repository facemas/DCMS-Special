<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Оценили запись блога');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    if (isset($_GET['return'])) {
        header('Refresh: 1; url=' . $_GET['return']);
    } else {
        header('Refresh: 1; url=./');
    }
    $doc->err(__('Запись не выбрана'));
    exit();
}
$id_blog = (int) $_GET['id'];
$q = $db->prepare("SELECT * FROM `blog` WHERE `id` = ?");
$q->execute(Array($id_blog));
if (!$blogs = $q->fetch()) {
    if (isset($_GET ['return'])) {
        header('Refresh: 1; url=' . $_GET['return']);
    } else {
        header('Refresh: 1; url=./');
    }
    $doc->err(__('Записи не существует'));
    exit;
}

$pages = new pages($db->query("SELECT COUNT(*) FROM `blog_like` WHERE `id_blog` = '" . $blogs['id'] . "'")->fetchColumn());
$pages->this_page();

$listing = new ui_components();
$listing->ui_comment = true; //подключаем css comments
$listing->ui_segment = true; //подключаем css segments
$listing->class = $dcms->browser_type == 'full' ? 'segments minimal large comments' : 'segments small comments';

$q = $db->query("SELECT * FROM `blog_like` WHERE `id_blog` = '" . $blogs['id'] . "' ORDER BY `id` DESC LIMIT " . $pages->limit);
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $message) {
        $ank = new user($message['id_user']);

        $post = $listing->post();
        $post->class = 'ui segment comment';
        $post->comments = true;
        $post->url = '/profile.view.php?id=' . $ank->id;
        $post->content = misc::times($message['time']);
        $post->login = $ank->nick();
        $post->avatar = $ank->getAvatar(80);
        $post->image_a_class = 'ui avatar';
    }
}
$listing->display(__('Нет результатов'));
$pages->display('?blog=' . $blogs['id'] . '&amp;'); // вывод страниц

$doc->ret(__('К записи'), '/blog/blog.php?blog=' . $blogs['id']);
