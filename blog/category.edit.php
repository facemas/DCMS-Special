<?php

include_once '../sys/inc/start.php';
$groups = groups::load_ini(); // загружаем массив групп
$doc = new document(4);
$doc->title = __('Редактирование категории');

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
    $doc->err(__('Категория не доступна для редактирования'));
    exit;
}

if (isset($_POST['save'])) {
    if (isset($_POST['name']) && isset($_POST['description'])) {
        $name = text::for_name($_POST['name']);
        $description = text::input_text($_POST['description']);
        
        if ($name && $name != $category['name']) {
            $dcms->log('Блоги', 'Изменение названия категории "' . $category['name'] . '" на [url=/blog/category.php?id=' . $category['id'] . ']"' . $name . '"[/url]');
            $category['name'] = $name;
            $res = $db->prepare("UPDATE `blog_cat` SET `name` = ? WHERE `id` = ? LIMIT 1");
            $res->execute(Array($category['name'], $category['id']));
            $doc->msg(__('Название категории успешно изменено'));
        }
        if ($description != $category['description']) {
            $category['description'] = $description;
            $res = $db->prepare("UPDATE `blog_cat` SET `description` = ? WHERE `id` = ? LIMIT 1");
            $res->execute(Array($category['description'], $category['id']));
            $doc->msg(__('Описание категории успешно изменено'));
            $dcms->log('Блоги', 'Изменение описания категории [url=/blog/category.php?id=' . $category['id'] . ']"' . $category['name'] . '"[/url]');
        }
    }
    if (isset($_POST['position'])) { // позиция
        $position = (int) $_POST['position'];
        if ($position != $category['position']) {
            $dcms->log('Блоги', 'Изменение позиции категории [url=/blog/category.php?id=' . $category['id'] . ']"' . $category['name'] . '"[/url] с ' . $category['position'] . ' на ' . $position);
            $category['position'] = $position;
            $res = $db->prepare("UPDATE `blog_cat` SET `position` = ? WHERE `id` = ? LIMIT 1");
            $res->execute(Array($category['position'], $category['id']));
            $doc->msg(__('Позиция категории успешно изменена'));
            $dcms->log('Блоги', 'Изменение позиции категории [url=/blog/category.php?id=' . $category['id'] . ']"' . $category['name'] . '"[/url] на ' . $position);
        }
    }
    if (isset($_POST['group_show'])) { // просмотр
        $group_show = (int) $_POST['group_show'];
        if (isset($groups[$group_show]) && $group_show != $category['group_show']) {
            $category['group_show'] = $group_show;
            $res = $db->prepare("UPDATE `blog_cat` SET `group_show` = ? WHERE `id` = ? LIMIT 1");
            $res->execute(Array($category['group_show'], $category['id']));
            $doc->msg(__('Просматривать категорию теперь разрешено группе "%s" и выше', groups::name($group_show)));
            $dcms->log('Блоги', 'Изменение прав на просмотр категории [url=/blog/category.php?id=' . $category['id'] . ']"' . $category['name'] . '"[/url] для группы ' . groups::name($group_show));
        }
    }
    if (isset($_POST['group_write'])) { // запись
        $group_write = (int) $_POST['group_write'];
        if (isset($groups[$group_write]) && $group_write != $category['group_write']) {
            if ($category['group_show'] > $group_write)
                $doc->err(__('Для того, чтобы создавать разделы группе "%s" сначала необходимо дать права на просмотр категории', groups::name($group_write)));
            else {
                $category['group_write'] = $group_write;
                $res = $db->prepare("UPDATE `blog_cat` SET `group_write` = ? WHERE `id` = ? LIMIT 1");
                $res->execute(Array($category['group_write'], $category['id']));
                $doc->msg(__('Создавать разделы теперь разрешено группе "%s" и выше', groups::name($group_write)));
                $dcms->log('Блоги', 'Изменение прав на создание разделов в категории [url=/blog/category.php?id=' . $category['id'] . ']"' . $category['name'] . '"[/url] для группы ' . groups::name($group_write));
            }
        }
    }
    if (isset($_POST['group_edit'])) { // редактирование
        $group_edit = (int) $_POST['group_edit'];
        if (isset($groups[$group_edit]) && $group_edit != $category['group_edit']) {
            if ($category['group_write'] > $group_edit) {
                $doc->err(__('Для изменения параметров категории группе "%s" сначала необходимо дать права на создание разделов', groups::name($group_edit)));
            } else {
                $category['group_edit'] = $group_edit;
                $res = $db->prepare("UPDATE `blog_cat` SET `group_edit` = ? WHERE `id` = ? LIMIT 1");
                $res->execute(Array($category['group_edit'], $category['id']));
                $doc->msg(__('Изменять параметры категории теперь разрешено группе "%s" и выше', groups::name($group_edit)));
                $dcms->log('Блоги', 'Изменение прав на изменение параметров категории [url=/blog/category.php?id=' . $category['id'] . ']"' . $category['name'] . '"[/url] для группы ' . groups::name($group_write));
            }
        }
    }
}
$doc->title = __('Редактирование категории "%s"', $category['name']); // шапка страницы
$form = new form(new url());
$form->text('name', __('Название'), $category['name']);
$form->textarea('description', __('Описание'), $category['description']);
$form->text('icon', __('Иконка'), $category['icon']);
$form->text('position', __('Позиция'), $category['position']);

$options = array();
foreach ($groups as $type => $value) {
    $options[] = array($type, $value['name'], $type == $category['group_show']);
}
$form->select('group_show', __('Просмотр разделов'), $options);

$options = array();
foreach ($groups as $type => $value) {
    $options[] = array($type, $value['name'], $type == $category['group_write']);
}

$form->select('group_write', __('Создание разделов'), $options);

$options = array();
foreach ($groups as $type => $value) {
    $options[] = array($type, $value['name'], $type == $category['group_edit']);
}
$form->select('group_edit', __('Изменение параметров'), $options);

$form->bbcode('* ' . __('Будьте внимательнее при установке доступа выше своего.'));
$form->button(__('Сохранить'), 'save');
$form->display();

$doc->opt(__('Оцистить категорию'), 'category.clear.php?id=' . $category['id']);
$doc->opt(__('Удалить категорию'), 'category.del.php?id=' . $category['id']);

if (isset($_GET['return'])) {
    $doc->ret(__('В категорию'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('В категорию'), 'category.php?id=' . $category['id']);
}

$doc->ret(__('Блоги'), './');
