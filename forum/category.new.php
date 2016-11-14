<?php

include_once '../sys/inc/start.php';
$doc = new document(5);
$doc->title = __('Новая категория');

if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['position'])) {
    $name = text::for_name($_POST['name']);
    $description = text::input_text($_POST['description']);
    $keywords = text::input_text($_POST['keywords']);
    $position = (int) $_POST['position'];

    if (!$name) {
        $doc->err(__('Введите название категории'));
    } else {
        $res = $db->prepare("INSERT INTO `forum_categories` (`name`, `description`, `keywords`, `position`, `group_edit`) VALUES (?, ?, ?, ?, ?)");
        $res->execute(Array($name, $description, $keywords, $position, max($user->group, 5)));
        $id_category = $db->lastInsertId();
        $dcms->log('Форум', 'Создание категории "' . $name . '"');
        $doc->msg(__('Категория успешно создана'));
        $doc->toReturn('?');
        $doc->act(__('Создать еще'), '?' . passgen());
        $doc->ret(__('В категорию'), 'category.php?id=' . $id_category);
        $doc->ret(__('Форум'), './');
        exit;
    }
}

$form = new form(new url());
$form->text('name', __('Название категории'));
$form->textarea('description', __('Описание'));
$form->text('keywords', __('Ключевые слова'));
$res = $db->query("SELECT MAX(`position`) AS max FROM `forum_categories`");
$k = ($row = $res->fetch()) ? $row['max'] : 0;
$form->text('position', __('Позиция'), $k + 1);
$form->button(__('Создать'));
$form->display();

$doc->ret(__('В форум'), './');
