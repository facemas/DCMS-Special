<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Новый раздел');

if (!isset($_GET['id_category']) || !is_numeric($_GET['id_category'])) {
    $doc->toReturn();
    $doc->err(__('Ошибка выбора категории'));
    exit;
}
$id_category = (int) $_GET['id_category'];

$q = $db->prepare("SELECT * FROM `forum_categories` WHERE `id` = ? AND `group_write` <= ?");
$q->execute(Array($id_category, $user->group));
if (!$category = $q->fetch()) {
    $doc->toReturn();
    $doc->err(__('В выбранной категории запрещено создавать разделы'));
    exit;
}


if (isset($_POST['name'])) {
    $name = text::for_name($_POST['name']);
    $description = text::input_text($_POST['description']);
    $keywords = text::input_text($_POST['keywords']);


    if (!$name) {
        $doc->err(__('Введите название раздела'));
    } else {
        $res = $db->prepare("INSERT INTO `forum_topics` (`id_category`, `time_create`,`time_last`, `name`, `description`, `keywords`, `group_show`, `group_write`, `group_edit`) VALUES (?,?,?,?,?,?,?,?,?)");
        $res->execute(Array($category['id'], TIME, TIME, $name, $description, $keywords, $category['group_show'], max($category['group_show'], 1), max($user->group, 4)));
        $id_topic = $db->lastInsertId();
        $doc->msg(__('Раздел успешно создан'));

        $dcms->log('Форум', 'Создание раздела [url=/forum/topic.php?id=' . $id_topic . ']' . $name . '[/url] в категории [url=/forum/category.php?id=' . $category['id'] . ']' . $category['name'] . '[/url]');

        $doc->toReturn(new url('topic.php', array('id' => $id_topic)));
        exit;
    }
}

$doc->title = $category['name'] . ' - ' . __('Новый раздел');

$form = new form(new url());
$form->text('name', __('Название раздела'));
$form->textarea('description', __('Описание'));
$form->text('keywords', __('Ключевые слова'));
$form->block('<div class="ui mini info message">' . __('Больше параметров можно будет настроить в параметрах раздела.') . '</div>');
$form->button(__('Создать раздел'));
$form->display();

if (isset($_GET['return']))
    $doc->ret(__('В категорию'), text::toValue($_GET['return']));
else
    $doc->ret(__('В категорию'), 'category.php?id=' . $category['id']);
