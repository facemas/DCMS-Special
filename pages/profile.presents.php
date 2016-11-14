<?php

include_once '../sys/inc/start.php';
$doc = new document(1); // инициализация документа для браузера
$doc->title = __('Подарки');

if (isset($_GET['id'])) {
    $ank = new user($_GET['id']);
} else {
    $ank = $user;
}

if (!$ank->group) {
    $doc->access_denied(__('Нет данных'));
}

$doc->title = ($user->id && $ank->id == $user->id) ? __('Мои подарки') : __('Подарки "%s"', $ank->nick);

$res = $db->prepare("SELECT COUNT(*) FROM `present_users` WHERE `id_user` = ?");
$res->execute(Array($ank->id));
$pages = new pages;
$pages->posts = $res->fetchColumn();

$q = $db->prepare("SELECT * FROM `present_users` WHERE `id_user` = ? ORDER BY `id` DESC LIMIT " . $pages->limit);
$q->execute(Array($ank->id));
$listing = new listing();
while ($item = $q->fetch()) {
    $ank_present = new user((int) $item['id_ank']);

    $post = $listing->post();

    if (is_file(H . $screen = '/sys/images/presents/' . $item['id_present'] . '.png')) {
        $post->title = '<img  src="' . $screen . '" style="max-width: 80px;"/> ';
    }
    $post->title .= '' . $ank_present->nick();
    $post->post .= text::toOutput($item['text']);
    $post->time = misc::when($item['time']);
    $post->url = '/profile.view.php?id=' . $ank_present->id;
    if ($user->id == $ank->id) {
        //$post->action('delete', 'item.delete.php?id=' . $item['id'] . "&amp;return=" . URL);
    }
}
$listing->display(__('Подарков нет'));

$pages->display('?id=' . $ank->id . '&amp;');

$doc->ret(__('В анкету'), "profile.view.php?id={$ank->id}");
$doc->ret(__('Личное меню'), '/menu.user.php');
