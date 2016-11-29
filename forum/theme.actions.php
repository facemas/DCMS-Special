<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Форум');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора темы'));
    exit;
}
$id_theme = (int) $_GET['id'];
$q = $db->prepare("SELECT `forum_themes`.* , `forum_categories`.`name` AS `category_name` , `forum_topics`.`name` AS `topic_name`, `forum_topics`.`group_write` AS `topic_group_write` FROM `forum_themes` LEFT JOIN `forum_categories` ON `forum_categories`.`id` = `forum_themes`.`id_category` LEFT JOIN `forum_topics` ON `forum_topics`.`id` = `forum_themes`.`id_topic` WHERE `forum_themes`.`id` = ? AND `forum_themes`.`group_show` <= ? AND `forum_topics`.`group_show` <= ? AND `forum_categories`.`group_show` <= ?");
$q->execute(Array($id_theme, $user->group, $user->group, $user->group));
if (!$theme = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Тема не доступна'));
    exit;
}

$doc->title = __('Тема %s - действия', $theme['name']);

$listing = new listing();

if ($theme['group_edit'] <= $user->group) {
    $post = $listing->post();
    $post->url = 'theme.status.php?id=' . $theme['id'];
    $post->title = $theme['group_write'] > $theme['topic_group_write'] ? __('Открыть тему') : __('Закрыть тему');
    $post->icon($theme['group_write'] > $theme['topic_group_write'] ? 'unlock' : 'lock');
}
if ($theme['group_edit'] <= $user->group) {
    $post = $listing->post();
    $post->url = 'theme.rename.php?id=' . $theme['id'];
    $post->title = __('Переименовать');
    $post->icon('pencil');
}
if ($theme['group_edit'] <= $user->group) {
    $post = $listing->post();
    $post->url = 'theme.move.php?id=' . $theme['id'];
    $post->title = __('Переместить');
    $post->icon('exchange');
}
if ($theme['group_edit'] <= $user->group) {
    $post = $listing->post();
    $post->url = 'theme.security.php?id=' . $theme['id'];
    $post->title = __('Разрешения');
    $post->icon('shield');
}
if ($theme['group_edit'] <= $user->group && $user->group >= 5) {
    $post = $listing->post();
    $post->url = 'theme.moderator.php?id=' . $theme['id'];
    $post->title = __('Назначить модератора');
    $post->icon('user-secret');
}
if (!$theme['id_vote'] && $theme['group_write'] <= $user->group && $user->group >= 2) {
    $post = $listing->post();
    $post->url = 'vote.new.php?id_theme=' . $theme['id'];
    $post->title = __('Создать голосование');
    $post->icon('bar-chart');
}
if ($theme['id_vote'] && $theme['group_write'] <= $user->group && $user->group >= 2) {
    $post = $listing->post();
    $post->url = 'vote.edit.php?id_theme=' . $theme['id'];
    $post->title = __('Изменить голосование');
    $post->icon('bar-chart');
}
if ($theme['group_edit'] <= $user->group && $user->group >= 2 || $user->id == $theme['id_moderator']) {
    $post = $listing->post();
    $post->url = 'theme.posts.delete.php?id=' . $theme['id'];
    $post->title = __('Удаление сообщений');
    $post->icon('window-close-o');
}
if ($theme['group_edit'] <= $user->group && $user->group >= 2) {
    $post = $listing->post();
    $post->url = 'theme.delete.php?id=' . $theme['id'];
    $post->title = __('Удаление темы');
    $post->icon('trash-o');
}

$listing->display();

$doc->ret(__('Вернуться в тему'), 'theme.php?id=' . $theme['id']);
$doc->ret($theme['topic_name'], 'topic.php?id=' . $theme['id_topic']);
$doc->ret($theme['category_name'], 'category.php?id=' . $theme['id_category']);
$doc->ret(__('Форум'), './');
