<?php
include_once '../../sys/inc/start.php';
dpanel::check_access();
$doc = new document(4);
$doc->title = __('Новая категория');
if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['position'])) 
{
$name = text::for_name($_POST['name']);
$description = text::input_text($_POST['description']);
$position = (int) $_POST['position'];
if (!$name) 
{
$doc->err(__('Введите название категории'));
}
else 
{
$res = $db->prepare("INSERT INTO `present_categories` (`name`, `description`, `position`)VALUES (?, ?, ?)");
$res->execute(Array($name, $description, $position));
$id_category = $db->lastInsertId();

$dcms->log('Подарки', 'Создание категории [url=/dpanel/presents/category.php?id=' . $id_category . ']' . $name . '[/url]');
$doc->msg(__('Категория успешно создана'));
$doc->act(__('Создать еще'), '?' . passgen());
$doc->ret(__('В категорию'), 'category.php?id=' . $id_category);
$doc->ret(__('Подарки'), './');
exit;
}
}
$form = new form('?' . passgen() . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
$form->text('name', __('Название категории'));
$form->textarea('description', __('Описание'));
$res = $db->query("SELECT MAX(`position`) AS max FROM `present_categories`");
$k = ($row = $res->fetch()) ? $row['max'] : 0;
$form->text('position', __('Позиция'), $k + 1);
$form->button(__('Создать'));
$form->display();
$doc->ret(__('В подарки'), './');