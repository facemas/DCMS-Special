<?php

include_once '../sys/inc/start.php';
$doc = new document();

$doc->title = __('Удаление категории');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора категории'));
    $doc->ret(__('Форум'), './');
    exit;
}
$id_category = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `forum_categories` WHERE `id` = ? AND `group_edit` <= ?");
$q->execute(Array($id_category, $user->group));

if (!$category = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Категория не доступна для удаления'));
    $doc->ret(__('Форум'), './');
    exit;
}

$doc->title = __('Удаление категории "%s"', $category['name']); // шапка страницы

if (isset($_POST['delete'])) {

    $q = $db->prepare("SELECT `id` FROM `forum_themes` WHERE `id_category` = ?");
    $q->execute(Array($category['id']));
    while ($theme = $q->fetch()) {
        // удаление всех файлов темы
        $dir = new files(FILES . '/.forum/' . $theme['id']);
        $dir->delete();
        unset($dir);
    }

    $res = $db->prepare("DELETE FROM `forum_topics`, `forum_themes` , `forum_messages`, `forum_history`, `forum_files`, `forum_vote`, `forum_vote_votes` USING `forum_topics` LEFT JOIN `forum_themes` ON `forum_themes`.`id_topic` = `forum_topics`.`id` LEFT JOIN `forum_messages` ON `forum_messages`.`id_topic` = `forum_topics`.`id` LEFT JOIN `forum_history` ON `forum_history`.`id_message` = `forum_messages`.`id` LEFT JOIN `forum_files` ON `forum_files`.`id_topic` = `forum_topics`.`id` LEFT JOIN `forum_vote` ON `forum_vote`.`id_theme` = `forum_themes`.`id` LEFT JOIN `forum_vote_votes` ON `forum_vote_votes`.`id_theme` = `forum_themes`.`id` LEFT JOIN `forum_views` ON `forum_vote_votes`.`id_theme` = `forum_themes`.`id` WHERE `forum_topics`.`id_category` = ?");
    $res->execute(Array($category['id']));

    $res = $db->prepare("DELETE FROM `forum_categories` WHERE `id` = ? LIMIT 1");
    $res->execute(Array($category['id']));

    header('Refresh: 1; url=./');
    $dcms->log('Форум', 'Удаление категории "' . $category['name'] . '"');
    $doc->msg(__('Категория успешно удалена'));
    $doc->ret(__('Форум'), './');
    exit;
}

$form = new form(new url());
$form->block('<div class="ui mini yellow message">' . __('Все данные, относящиеся к данной категории будут безвозвратно удалены.') . '</div>');
$form->button(__('Удалить'), 'delete');
$form->display();

$doc->act(__('Параметры категории'), 'category.edit.php?id=' . $category['id']);
$doc->ret(__('В категорию'), 'category.php?id=' . $category['id']);
$doc->ret(__('Форум'), './');
?>