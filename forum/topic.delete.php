<?php

include_once '../sys/inc/start.php';
$doc = new document();

$doc->title = __('Удаление раздела');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора раздела'));
    exit;
}
$id_topic = (int) $_GET['id'];
$q = $db->prepare("SELECT * FROM `forum_topics` WHERE `id` = ? AND `group_edit` <= ?");
$q->execute(Array($id_topic, $user->group));
if (!$topic = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Раздел не доступен для удаления'));
    exit;
}

$q = $db->prepare("SELECT * FROM `forum_categories` WHERE `id` = ?");
$q->execute(Array($topic['id_category']));
$category = $q->fetch();

if (isset($_POST['delete'])) {
    $q = $db->prepare("SELECT `id` FROM `forum_themes` WHERE `id_topic` = ?");
    $q->execute(Array($topic['id']));
    while ($theme = $q->fetch()) {
        // удаление всех файлов темы
        $dir = new files(FILES . '/.forum/' . $theme['id']);
        $dir->delete();
        unset($dir);
    }

    $res = $db->prepare("DELETE FROM `forum_themes` , `forum_messages`, `forum_history`,  `forum_vote`, `forum_vote_votes` USING `forum_themes` LEFT JOIN `forum_messages` ON `forum_messages`.`id_theme` = `forum_themes`.`id` LEFT JOIN `forum_history` ON `forum_history`.`id_message` = `forum_messages`.`id` LEFT JOIN `forum_vote` ON `forum_vote`.`id_theme` = `forum_themes`.`id` LEFT JOIN `forum_vote_votes` ON `forum_vote_votes`.`id_theme` = `forum_themes`.`id` LEFT JOIN `forum_views` ON `forum_vote_votes`.`id_theme` = `forum_themes`.`id` WHERE `forum_themes`.`id_topic` = ?");
    $res->execute(Array($topic['id']));
    $res = $db->prepare("DELETE FROM `forum_topics` WHERE `id` =? LIMIT 1");
    $res->execute(Array($topic['id']));
    header('Refresh: 1; url=category.php?id=' . $topic['id_category']);

    $dcms->log('Форум', 'Удаление раздела из категории [url=/forum/category.php?id=' . $category['id'] . ']' . $category['name'] . '[/url]');

    $doc->msg(__('Рездел успешно удален'));
    exit;
}


$doc->title = __('Удаление раздела "%s"', $topic['name']);

$form = new form(new url());
$form->block('<div class="ui mini yellow message">' . __('Все данные, относящиеся к данному разделу будут безвозвратно удалены.') . '</div>');
$form->button(__('Удалить'), 'delete');
$form->display();

$doc->opt(__('Параметры раздела'), 'topic.edit.php?id=' . $topic['id']);
$doc->ret(__('В раздел'), 'topic.php?id=' . $topic['id']);
$doc->ret(__('Форум'), './');
