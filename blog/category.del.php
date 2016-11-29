<?php

include_once '../sys/inc/start.php';

$doc = new document(4);
$doc->title = __('Удаление категории');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора категории'));
    $doc->ret(__('Блоги'), './');
    exit;
}

$id_category = (int) $_GET['id'];
$q = $db->prepare("SELECT * FROM `blog_cat` WHERE `id` = ? AND `group_edit` <= ?");
$q->execute(Array($id_category, $user->group));

if (!$category = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Категория не доступна для удаления'));
    $doc->ret(__(Блоги), './');
    exit;
}

$doc->title = __('Удаление категории "%s"', $category['name']); // шапка страницы

if (isset($_POST['delete'])) {

    $q = $db->prepare("SELECT `id` FROM `blog` WHERE `id_cat` = ?");
    $q->execute(Array($category['id']));
    while ($theme = $q->fetch()) {
// удаление всех файлов темы
        $dir = new files(FILES . '/.blog/' . $theme['id']);
        $dir->delete();
        unset($dir);
    }
    $res = $db->prepare("DELETE FROM `bloge_cat`, `blog` , `bloge_com` USING `bloge_cat` LEFT JOIN `blog` ON `blog`.`id_cat` = `bloge_cat`.`id` LEFT JOIN `bloge_com` ON `bloge_com`.`id_cat` = `bloge_cat`.`id` LEFT JOIN `bloge_views` ON `bloge_views`.`id_bloge` = `blog`.`id` WHERE `bloge_cat`.`id_cat` = ?");
    $res->execute(Array($category['id']));
    $res = $db->prepare("DELETE FROM `blog_cat` WHERE `id` = ? LIMIT 1");
    $res->execute(Array($category['id']));
    header('Refresh: 1; url=./');
    $dcms->log('Блоги', 'Удаление категории "' . $category['name'] . '"');
    $doc->msg(__('Категория успешно удалена'));
    $doc->ret(__('Блоги'), './');
    exit;
}

$form = new form(new url());
$form->block("<div class='ui mini info message'>" . __('Все данные, относящиеся к данной категории будут безвозвратно удалены') . "</div>");

$form->button(__('Удалить'), 'delete');
$form->display();
$doc->opt(__('Параметры категории'), 'category.edit.php?id=' . $category['id']);
$doc->ret(__('В категорию'), 'category.php?id=' . $category['id']);
$doc->ret(__('Блоги'), './');
?>