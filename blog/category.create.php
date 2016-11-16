<?php

include_once '../sys/inc/start.php';
$doc = new document(5);
$doc->title = __('Новая категория');

if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['position'])) {
    $name = text::for_name($_POST['name']);
    $icon = text::for_name($_POST['icon']);
    $description = text::input_text($_POST['description']);
    $position = (int) $_POST['position'];

    if (!$name) {
        $doc->err(__('Введите название категории'));
    } else {
        $res = $db->prepare("INSERT INTO `blog_cat` (`name`, `description`, `icon`, `position`, `group_edit`) VALUES (?, ?, ?, ?, ?)");
        $res->execute(Array($name, $description, $icon, $position, max($user->group, 5)));
        $id_category = $db->lastInsertId();

        $doc->msg(__('Категория успешно создана'));
        $doc->opt(__('Создать еще'), '?' . passgen(), false, '<i class="fa fa-plus fa-fw"></i>');
        $doc->opt(__('В категорию'), 'category.php?id=' . $id_category);
        $doc->ret(__('Блоги'), './');
        exit;
    }
}
$form = new form('?' . passgen() . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
$form->text('name', __('Название категории'));
$form->text('icon', __('Иконка категории'), 'folder-o');
$form->textarea('description', __('Описание'));

$res = $db->query("SELECT MAX(`position`) AS max FROM `blog_cat`");
$k = ($row = $res->fetch()) ? $row['max'] : 0;

$form->text('position', __('Позиция'), $k + 1);
$form->button(__('Создать'));
$form->display();

$doc->ret(__('Блоги'), './');
