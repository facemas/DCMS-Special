<?php
include_once '../../sys/inc/start.php';
dpanel::check_access();
$doc = new document(4);
$doc->title = __('Удалить');
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
{
header('Refresh: 1; url=./');
$doc->err(__('Ошибка выбора подарка'));
exit;
}
$id_present = (int)$_GET['id'];
$q = $db->prepare("SELECT * FROM `present_items` WHERE `id` = ?");
$q->execute(Array($id_present));
if (!$item = $q->fetch()) 
{
header('Refresh: 1; url=./');
$doc->err(__('Подарок не доступна'));
exit;
}
if (isset($_POST['delete']))
{
$res = $db->prepare("DELETE FROM `present_items` WHERE `id` = ? LIMIT 1");
$res->execute(Array($id_present));
$dcms->log('Подарки', 'Удаление подарка ' . $item['name']);
$doc->msg(__('Подарок успешно удален'));
if (isset($_GET['return'])) 
{
header('Refresh: 1; url=' . $_GET['return']);
$doc->ret(__('Вернуться'), text::toValue($_GET['return']));
} 
else 
{
header('Refresh: 1; url=category.php?id=' . $item['id_category'] . '&' . passgen());
$doc->ret(__('В категорию'), 'category.php?id=' . $item['id_category']);
}
exit;
}
$doc->title .= ' - ' . $item['name'];
$form = new form('?id=' . $id_present . '&' . passgen() . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
$form->captcha();
$form->button(__('Удалить'), 'delete');
$form->display();
if (isset($_GET['return']))
$doc->ret(__('Вернуться'), text::toValue($_GET['return']));
else
$doc->ret(__('В категорию'), 'category.php?id=' . $item['id_category']);