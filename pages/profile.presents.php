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

$listing = new ui_components();
$listing->ui_segment = true; //подключаем css segments
$listing->ui_list = true; //подключаем css segments
$listing->class = 'ui segments list';

$q = $db->prepare("SELECT * FROM `present_users` WHERE `id_user` = ? ORDER BY `id` DESC LIMIT " . $pages->limit);
$q->execute(Array($ank->id));

while ($item = $q->fetch()) {
    $ank = new user($item['id_ank']);

    $post = $listing->post();
    $post->class = 'ui segment item';
    $post->ui_label = true;
    $post->ui_image = true;

    if (is_file(H . $screen = '/sys/images/presents/' . $item['id_present'] . '.png')) {
        $post->avatar = $screen;
        $post->image_class = 'ui tiny image';
    }
    $post->content = '
        <a class="header" href="/profile.view.php?id=' . $ank->id . '">' . $ank->nick() . '</a>
        <div class="description">' . text::toOutput($item['text']) . '</div>
        <div class="description" style="color: grey;">' . misc::times($item['time']) . '</div>';

    $post->url = '/profile.view.php?id=' . $ank->id;

    if ($user->id == $ank->id) {
        //$post->action('delete', 'item.delete.php?id=' . $item['id'] . "&amp;return=" . URL);
    }
}
$listing->display(__('Подарков нет'));

$pages->display('?id=' . $ank->id . '&amp;');

$doc->ret($ank->login, "profile.view.php?id={$ank->id}");
