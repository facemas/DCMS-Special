<?php

include_once '../sys/inc/start.php';
$doc = new document(5);
$doc->title = __('Очистка категории');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора категории'));
    exit;
}

$id_category = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `blog_cat` WHERE `id` = ? AND `group_edit` <= ?");
$q->execute(Array($id_category, $user->group));

if (!$category = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Категория не доступна для чистки'));
    exit;
}

$doc->title = __('Чистка категории "%s"', $category['name']);

$res = $db->prepare("SELECT COUNT(*) FROM `blog` WHERE `id_cat` = ?");
$res->execute(Array($category['id']));

$count = $res->fetchColumn();

if (isset($_POST['clear'])) {
    $q = $db->prepare("DELETE FROM `blog` WHERE `id_cat` = ?");
    $q->execute(Array($category['id']));
    $doc->msg('' . $count . ' из ' . $count . ' записей были удалены из категории');
}

$form = new form(new url());

$form->bbcode(__('Всего записей в категории: %s', '[b]' . $count . '[/b]'));
$form->block("<div class='ui mini info message'>" . __('Данные будут безвозвратно удалены') . "</div>");
$form->button(__('Очистить'), 'clear');
$form->display();
$doc->opt(__('Параметры категории'), 'category.edit.php?id=' . $category['id']);
$doc->ret(__('В категорию'), 'category.php?id=' . $category['id']);
$doc->ret(__('Блоги'), './');
