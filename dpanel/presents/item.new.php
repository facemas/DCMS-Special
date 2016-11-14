<?php

include_once '../../sys/inc/start.php';
dpanel::check_access();
$doc = new document(4);
$doc->title = __('Новый подарок');
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
if (isset($_POST['name'])) {
    $name = text::for_name($_POST['name']);
    $ball = (INT) $_POST['ball'];
    $res = $db->prepare("INSERT INTO `present_items` (`name`, `ball`, `id_category`)VALUES (?, ?, ?)");
    $res->execute(Array($name, $ball, $id_cat));
    $id_item = $db->lastInsertId();
    $dcms->log('Подарки', 'Создание подарка [url=/dpanel/presents/item.php?id=' . $id_item . ']' . $name . '[/url] в категории [url=/dpanel/presents/category.php?id=' . $category['id'] . ']' . $category['name'] . '[/url]');
    $doc->msg(__('Подарок успешно создано'));
    if (isset($_GET['return'])) {
        header('Refresh: 1; url=' . $_GET['return']);
        $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
    } else {
        header('Refresh: 1; url=item.php?id=' . $id_item . '&' . SID);
        $doc->ret(__('В подарок'), 'item.php?id=' . $id_item);
    }
    exit;
}
$form = new form('?id=' . $id_cat . '&' . passgen() . (isset($_GET['return']) ? '&return=' . urlencode($_GET['return']) : null));
$form->text('name', __('Название подарка'));
$form->text('ball', __('Стоимость подарка'));
$form->button(__('Создать'));
$form->display();
if (isset($_GET['return']))
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
else
    $doc->ret(__('В категорию'), 'category.php?id=' . $category['id']);