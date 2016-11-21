<?php

include_once '../../sys/inc/start.php';

dpanel::check_access();

$doc = new document(4);
$doc->title = __('Подарки');
$doc->ret(__('К категориям'), './');

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

$res = $db->prepare("SELECT COUNT(*) FROM `present_items` WHERE `id_category` = ?");
$res->execute(Array($category['id']));

$pages = new pages;
$pages->posts = $res->fetchColumn();

$q = $db->prepare("SELECT * FROM `present_items` WHERE `id_category` = ? ORDER BY `id` DESC LIMIT " . $pages->limit);
$q->execute(Array($category['id']));

$listing = new ui_components();
$listing->ui_segment = true; //подключаем css segments
$listing->class = 'ui segments';

while ($item = $q->fetch()) {
    $post = $listing->post();
    $post->class = 'ui segment';
    $post->ui_label = true;
    $post->list = true;

    if (is_file(H . $screen = '/sys/images/presents/' . $item['id'] . '.png')) {
        $post->image = $screen;
    } else {
        $post->post = __('No foto');
    }

    $post->title .= text::toValue($item['name']);
    $post->url = "item.edit.php?id=" . $item['id'] . "&amp;return=" . URL;
    $post->time = __('%s баллов', ($item['ball'] == 0 ? __('Бесплатно') : $item['ball']));
    $post->counter .= " <span data-tooltip='" . __('Добавить изображение') . "' data-position='top right'><a href='item.image.php?id=$item[id]&amp;return=" . URL . "'><i class='fa fa-image fa-fw'></i></a> </span>";
    $post->counter .= " <span data-tooltip='" . __('Удалить') . "' data-position='bottom right'><a href='item.delete.php?id=$item[id]&amp;return=" . URL . "'><i class='fa fa-trash-o fa-fw'></i></a> </span>";
}
$listing->display(__('Подарков нет'));

$pages->display('?id=' . $id_cat . '&amp;');

$doc->opt(__('Создать подарок'), 'item.new.php?id=' . $category['id'] . "&amp;return=" . URL, false, '<i class="fa fa-plus fa-fw"></i>');
$doc->opt(__('Параметры категории'), 'category.edit.php?id=' . $category['id'] . "&amp;return=" . URL, false, '<i class="fa fa-edit fa-fw"></i>');
