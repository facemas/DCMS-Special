<?php

include_once '../sys/inc/start.php';
$doc = new document(2);
$doc->title = __('Удаление темы');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $doc->toReturn();
    $doc->err(__('Ошибка выбора темы'));
    exit;
}
$id_theme = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `forum_themes` WHERE `id` = ? AND `group_edit` <= ?");
$q->execute(Array($id_theme, $user->group));
if (!$theme = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Тема не доступна для редактирования'));
    exit;
}


$q = $db->prepare("SELECT * FROM `forum_topics` WHERE `id` = ? LIMIT 1");
$q->execute(Array($theme['id_topic']));

$topic = $q->fetch();

$doc->title .= ' "' . $theme['name'] . '"';

if (isset($_POST['delete'])) {

    $res = $db->prepare("DELETE FROM `forum_themes` WHERE `id` = ? LIMIT 1");
    $res->execute(Array($theme['id']));

    $res = $db->prepare("DELETE FROM `forum_messages`, `forum_history` USING `forum_messages` LEFT JOIN `forum_history` ON `forum_history`.`id_message` = `forum_messages`.`id` WHERE `forum_messages`.`id_theme` = ?");
    $res->execute(Array($theme['id']));

    $res = $db->prepare("DELETE FROM `forum_vote` WHERE `id_theme` = ?");
    $res->execute(Array($theme['id']));

    $res = $db->prepare("DELETE FROM `forum_vote_votes` WHERE `id_theme` = ?");
    $res->execute(Array($theme['id']));

    $res = $db->prepare("DELETE FROM `forum_views` WHERE `id_theme` = ?");
    $res->execute(Array($theme['id']));

    // удаление всех файлов темы
    $dir = new files(FILES . '/.forum/' . $theme['id']);
    $dir->delete();
    unset($dir);

    header('Refresh: 1; url=topic.php?id=' . $theme['id_topic']);
    $doc->msg(__('Тема успешно удалена'));
    $dcms->log('Форум', 'Удаление темы "' . $theme['name'] . '" из раздела [url=/forum/topic.php?id=' . $topic['id'] . ']' . $topic['name'] . '[/url]');
    exit;
}

$form = new form(new url());
$form->block('<div class="ui mini yellow message">' . __('Все данные, относящиеся к данной теме будут безвозвратно удалены.') . '</div>');
$form->button(__('Удалить'), 'delete');
$form->display();

if (isset($_GET['return'])) {
    $doc->ret(__('В тему'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('В тему'), 'theme.php?id=' . $theme['id']);
}

$doc->ret(__('В раздел'), 'topic.php?id=' . $theme['id_topic']);
$doc->ret(__('В категорию'), 'category.php?id=' . $theme['id_category']);
$doc->ret(__('Форум'), './');
?>