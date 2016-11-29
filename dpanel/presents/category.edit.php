<?php

include_once '../../sys/inc/start.php';

dpanel::check_access();

$doc = new document(4);
$doc->title = __('Параметры');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора категории'));
    exit;
}

$id_cat = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `present_categories` WHERE `id` = ?");
$q->execute(Array($id_cat));

if (!$category = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Категория не доступна'));
    exit;
}
$doc->title .= ' - ' . $category['name'];

if (isset($_POST['save'])) {
    $name = text::for_name($_POST['name']);
    $description = text::for_name($_POST['description']);
    $res = $db->prepare("UPDATE `present_categories` SET `name` = ?, `description` = ? WHERE `id` = ? LIMIT 1");
    $res->execute(Array($name, $description, $id_cat));
    $doc->msg(__('Изменения сохранены'));

    if (isset($_GET['return'])) {
        header('Refresh: 1; url=' . $_GET['return']);
        $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
    } else {
        header('Refresh: 1; url=category.php?id=' . $item['id_category'] . '&' . passgen());
        $doc->ret(__('В категорию'), 'category.php?id=' . $item['id_category']);
    }
    exit;
}

$form = new form('?id=' . $id_cat . '&' . passgen() . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
$form->text('name', __('Название категории'), $category['name']);
$form->textarea('description', __('Описание'), $category['description']);
$form->button(__('Сохранить'), 'save');
$form->display();

$doc->opt(__('Удалить категорию'), 'category.delete.php?id=' . $id_cat . "&amp;return=" . URL);

if (isset($_GET['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('В категорию'), 'category.php?id=' . $category['id']);
}