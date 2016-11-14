<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Подарки');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора категории'));
    exit;
}

if (!isset($_GET['user']) || !is_numeric($_GET['user'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора пользователя'));
    exit;
}

$id_cat = (int) $_GET['id'];
$ank = new user((int) $_GET['user']);

if (!$ank->group) {
    header('Refresh: 1; url=/profile.view.php?id=' . $user->id . '&' . passgen());
    $doc->access_denied(__('Нет данных'));
}

if ($ank->id == $user->id) {
    header('Refresh: 1; url=/profile.view.php?id=' . $user->id . '&' . passgen());
    $doc->err(__('Ошибка операция подарка'));
    exit;
}

$q = $db->prepare("SELECT * FROM `present_categories` WHERE `id` = ?");
$q->execute(Array($id_cat));
if (!$category = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Категория не доступна'));
    exit;
}

$doc->title .= ' - ' . $category['name'];

$res = $db->prepare("SELECT COUNT(*) FROM `present_items` WHERE `id_category` = ?");
$res->execute(Array($category['id']));
$pages = new pages;
$pages->posts = $res->fetchColumn();

$q = $db->prepare("SELECT * FROM `present_items` WHERE `id_category` = ? ORDER BY `id` DESC LIMIT " . $pages->limit);
$q->execute(Array($category['id']));
$listing = new listing();
while ($item = $q->fetch()) {
    $post = $listing->post();

    if (is_file(H . $screen = '/sys/images/presents/' . $item['id'] . '.png')) {
        $post->title = '<img src="' . $screen . '"  style="max-width: 80px;"> ';
    } else {
        $post->post = __('Изображение отсутствует');
    }

    $post->title .= text::toValue($item['name']);
    $post->url = "item.php?id=" . $item['id'] . "&user=" . $ank->id;

    $post->counter = __('%s', ($item['ball'] == 0 ? __('Бесплатно') : "<i class='fa fa-gg-circle fa-fw'></i> $item[ball]"));
}
$listing->display(__('Подарков нет'));

$pages->display('?id=' . $id_cat . '&amp;');

$doc->ret(__('К категориям'), './?user=' . $ank->id);
$doc->ret(__('В анкету'), "/profile.view.php?id={$ank->id}");
