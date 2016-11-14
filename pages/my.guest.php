<?php

include_once '../sys/inc/start.php';

$doc = new document(1);
$doc->title = __('Гости');
if (isset($_GET['truncate'])) {
    $res = $db->prepare("DELETE FROM `my_guests` WHERE `id_ank` = ? ");
    $res->execute(Array($user->id));
    $doc->msg(__('Список гостей очищен'));
}

$doc->title .= __(': "%s"', $user->login);

$pages = new pages($db->query("SELECT COUNT(*) FROM `my_guests` WHERE `id_ank` = '" . $user->id . "'")->fetchColumn());
$pages->this_page();
$listing = new listing();
$q = $db->query("SELECT * FROM `my_guests` WHERE `id_ank` = '" . $user->id . "' ORDER BY `time` DESC LIMIT " . $pages->limit);
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $guests) {
        $ank = new user($guests['id_user']);
        $post = $listing->post();
        $post->hightlight = $guests['read'];
        $post->url = '/profile.view.php?id=' . $ank->id;
        $post->icon = $ank->icon();
        $post->title = $ank->nick();
        $post->time = misc::when($guests['time']);
        $post->counter = $guests['count'];

        $res = $db->prepare("UPDATE `my_guests` SET `read` = ? WHERE `id` = ? LIMIT 1");
        $res->execute(Array(0, $guests['id']));
    }
}
$listing->display(__('Гости отсутствуют'));
$pages->display('?'); // вывод страниц

$doc->ret(__('Профиль'), '/profile.view.php?id=' . $user->id);
$doc->opt(__('Очистить список гостей'), "?truncate", false, '<i class="fa fa-trash-o fa-fw"></i>');
