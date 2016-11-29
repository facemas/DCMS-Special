<?php

include_once '../sys/inc/start.php';

$doc = new document(1);
$doc->title = __('Подарки - Категории');

if (!isset($_GET['user']) || !is_numeric($_GET['user'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора пользователя'));
    exit;
}

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

$pages = new pages();
$res = $db->query("SELECT COUNT(*) FROM `present_categories`");
$pages->posts = $res->fetchColumn();

$listing = new ui_components();
$listing->ui_segment = true; //подключаем css segments
$listing->class = 'ui segments';

$q = $db->query("SELECT * FROM `present_categories` ORDER BY `position` ASC LIMIT " . $pages->limit);

while ($category = $q->fetch()) {
    $post = $listing->post();
    $post->class = 'ui segment';
    $post->ui_label = true;
    $post->list = true;
    $post->url = "category.php?id=$category[id]&user=" . $ank->id;
    $post->title = text::toValue($category['name']);
    $post->icon('th-large');
    $post->post = text::for_opis($category['description']);
    $res = $db->query("SELECT COUNT(*) FROM `present_items` WHERE `id_category` = '$category[id]'");
    $post->counter = $res->fetchColumn();
}

$listing->display(__('Нет результатов'));
$pages->display('?');

$doc->ret(__('В анкету'), "/profile.view.php?id={$ank->id}");
