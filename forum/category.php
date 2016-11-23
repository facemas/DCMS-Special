<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Форум');
$doc->ret(__('К категориям'), './');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора категории'));
    exit;
}

$id_cat = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `forum_categories` WHERE `id` = ? AND `group_show` <= ?");
$q->execute(Array($id_cat, $user->group));
if (!$category = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Категория не доступна'));
    exit;
}


$doc->title .= ' - ' . $category['name'];
$doc->description = $category['description'];
$doc->keywords = $category['keywords'];

$res = $db->prepare("SELECT COUNT(*) FROM `forum_topics` WHERE `id_category` = ? AND `group_show` <= ?");
$res->execute(Array($category['id'], $user->group));
$pages = new pages;
$pages->posts = $res->fetchColumn(); // количество категорий форума

$q = $db->prepare("SELECT * FROM `forum_topics` WHERE `id_category` = ? AND `group_show` <= ? ORDER BY `time_last` DESC LIMIT " . $pages->limit);
$q->execute(Array($category['id'], $user->group));
$listing = new listing();
while ($topics = $q->fetch()) {
    $res = $db->prepare("SELECT COUNT(*) FROM `forum_themes` WHERE `id_topic` = ? AND `group_show` <= ?");
    $res->execute(Array($topics['id'], $user->group));
    $themesCount = $res->fetchColumn();

    $post = $listing->post();
    $post->icon('object-ungroup');
    $post->title = text::toValue($topics['name']);
    $post->content = text::for_opis($topics['description']);
    $post->url = "topic.php?id={$topics['id']}";
    $post->counter = $themesCount;
}
$listing->display(__('Доступных Вам разделов нет'));


$pages->display('?id=' . $id_cat . '&amp;'); // вывод страниц

if ($category['group_write'] <= $user->group) {
    $doc->opt(__('Создать раздел'), 'topic.new.php?id_category=' . $category['id'] . "&amp;return=" . URL, false, '<i class="fa fa-plus fa-fw"></i>');
}
if ($category['group_edit'] <= $user->group) {
    $doc->opt(__('Параметры категории'), 'category.edit.php?id=' . $category['id'] . "&amp;return=" . URL, false, '<i class="fa fa-edit fa-fw"></i>');
}
if ($user->group >= 5) {
    $doc->opt(__('Статистика'), 'category.stat.php?id=' . $category['id'] . "&amp;return=" . URL, false, '<i class="fa fa-pie-chart fa-fw"></i>');
}