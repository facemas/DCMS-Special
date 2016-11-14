<?php

include_once '../sys/inc/start.php';

if (AJAX) {
    $doc = new document_json(1);
} else {
    $doc = new document(1);
}

$doc->title = __('Мои уведомления');

$res = $db->prepare("SELECT COUNT(*) FROM `notification` WHERE `id_user` = ? AND `is_read` = '0'");
$res->execute(Array($user->id));
$user->not_new_count = $res->fetchColumn();

$id_kont = $user->id;
$ank = new user($id_kont);

$pages = new pages ();
$res = $db->prepare("SELECT COUNT(*) FROM `notification` WHERE `id_user` = ?");
$res->execute(Array($user->id));
$pages->posts = $res->fetchColumn(); // количество уведомлений


$q = $db->prepare("SELECT * FROM `notification` WHERE `id_user` = ? ORDER BY `id` DESC LIMIT " . $pages->limit);
$q->execute(Array($user->id));

// отметка о прочтении писем
$res = $db->prepare("UPDATE `notification` SET `is_read` = '1' WHERE `id_user` = ?");
$res->execute(Array($user->id));

// уменьшаем кол-во непрочитанных писем на количество помеченных как прочитанные
$user->not_new_count = $user->not_new_count - $res->rowCount();

$id_after = false;
$listing = new ui_components();
$listing->ui_feed = true; //подключаем css feed
$listing->class = 'small feed listing';

if ($arr = $q->fetchAll()) {
    foreach ($arr AS $not) {
        $ank2 = new user((int) $not['id_sender']);
        $post = $listing->post();
        $post->class = 'event';
        $post->feed = true;

        $post->id = 'not_post_' . $not['id'];
        $post->login = $ank2->login;
        $post->url = '/profile.view.php?id=' . $ank2->id;
        $post->avatar = $ank2->getAvatar();
        $post->image_a_class = 'label';
        $post->content = text::toOutput($not['mess']);
        //$post->highlight = !$not['is_read'];
        $post->time = misc::when($not['time']);

        if ($doc instanceof document_json) {
            $doc->add_post($post, $id_after);
        }

        $id_after = $post->id;
    }
}
if (isset($form)) {
    $listing->setForm($form);
}
$listing->setAjaxUrl('?id=' . $ank->id . '&amp;page=' . $pages->this_page);

if ($doc instanceof document_json && !$arr) {
    $post = new listing_post(__('Уведомления отсутствуют'));
    $post->icon('clone');
    $doc->add_post($post);
}

$listing->display(__('Уведомления отсутствуют'));

if ($doc instanceof document_json) {
    $doc->set_pages($pages);
}

$pages->display('?id=' . $ank->id . '&amp;'); // вывод страниц

$doc->ret(__('Личное меню'), '/menu.user.php');
exit();

