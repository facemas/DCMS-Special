<?php

include_once '../../sys/inc/start.php';
dpanel::check_access();

$doc = new document(4);
$doc->title = __('Редактирование подарка');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора подарка'));
    exit;
}

$id_present = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `present_items` WHERE `id` = ?");
$q->execute(Array($id_present));

if (!$item = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Подарок не доступна'));
    exit;
}

$doc->title .= ' - ' . $item['name'];

if (isset($_POST['name'])) {
    $name = text::for_name($_POST['name']);
    $ball = (INT) $_POST['ball'];
    $res = $db->prepare("UPDATE `present_items` SET `name` = ?, `ball` = ? WHERE `id` = ? LIMIT 1");
    $res->execute(Array($name, $ball, $id_present));
    $dcms->log('Подарки', 'Изменение подарка [url=/dpanel/presents/item.php?id=' . $id_present . ']' . $name . '[/url]');
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

$form = new form('?id=' . $id_present . '&' . passgen() . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
$form->text('name', __('Название подарка'), $item['name']);
$form->text('ball', __('Стоимость подарка'), $item['ball']);
$form->button(__('Сохранить'));
$form->display();

$doc->opt(__('Изображение подарка'), 'item.image.php?id=' . $item['id'] . "&amp;return=" . URL);
$doc->opt(__('Удалить подарок'), 'item.delete.php?id=' . $item['id'] . "&amp;return=" . URL);

if (isset($_GET['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('В категорию'), 'category.php?id=' . $item['id_category']);
}