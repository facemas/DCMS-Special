<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Друзья');

$ank = new user(@$_GET['id']);

if (!$ank->id) {
    $doc->toReturn();
    $doc->err(__('Нет данных'));
    exit;
}

if (!$ank->is_friend($user) && !$ank->vis_friends) {
    $doc->toReturn();
    $doc->err(__('Доступ к данной странице ограничен'));
    exit;
}
$doc->title = __('Друзья %s', $ank->login);

$posts = array();

$pages = new pages;

$res = $db->prepare("SELECT COUNT(*) FROM `friends` WHERE `id_user` = ? AND `confirm` = '1'");
$res->execute(Array($ank->id));
$pages->posts = $res->fetchColumn();

$q = $db->prepare("SELECT * FROM `friends` WHERE `id_user` = ? AND `confirm` = '1' ORDER BY `time` DESC LIMIT " . $pages->limit);
$q->execute(Array($ank->id));

$listing = new listing();
while ($arr = $q->fetchAll()) {
    foreach ($arr AS $friend) {
        $friends = new user($friend['id_friend']);
        $post = $listing->post();
        $post->title = $friends->nick();
        $post->url = '/profile.view.php?id=' . $friends->id;
        $post->image = $friends->getAvatar();
    }
}

$listing->display(__('У пользователя "%s" еще нет друзей', $ank->login));

$pages->display('?id=' . $ank->id . '&amp;'); // вывод страниц

$doc->ret(__('Анкета "%s"', $ank->login), '/profile.view.php?id=' . $ank->id);
