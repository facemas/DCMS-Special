<?php
include_once '../../sys/inc/start.php';
dpanel::check_access();
$doc = new document(4);
$doc->title = __('Удаление категория');
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
{
header('Refresh: 1; url=./');
$doc->err(__('Ошибка выбора категории'));
exit;
}
$id_cat = (int)$_GET['id'];
$q = $db->prepare("SELECT * FROM `present_categories` WHERE `id` = ?");
$q->execute(Array($id_cat));
if (!$category = $q->fetch()) 
{
header('Refresh: 1; url=./');
$doc->err(__('Категория не доступна'));
exit;
}
if (isset($_POST['delete']))
{
$res = $db->prepare("DELETE FROM `present_categories` WHERE `id` = ? LIMIT 1");
$res->execute(Array($id_cat));
$res = $db->prepare("DELETE FROM `present_items` WHERE `id_category` = ?");
$res->execute(Array($id_cat));

$dcms->log('Подарки', 'Удаление категория ' . $category['name']);
$doc->msg(__('Категория успешно удален'));
header('Refresh: 1; url=./?' . passgen());
$doc->ret(__('В подарки'), './');
exit;
}
$doc->title .= ' - ' . $category['name'];
$form = new form('?id=' . $id_cat . '&' . passgen() . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
$form->captcha();
$form->button(__('Удалить'), 'delete');
$form->display();
if (isset($_GET['return']))
$doc->ret(__('Вернуться'), text::toValue($_GET['return']));
else
$doc->ret(__('В подарки'), './');