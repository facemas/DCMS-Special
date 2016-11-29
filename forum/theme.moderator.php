<?php

include_once '../sys/inc/start.php';
$doc = new document(5);
$doc->title = __('Назначить модератора темы');

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

if ($theme['id_moderator']) {
    $doc->act(__('Снять модератора'), '?id=' . $theme['id'] . '&amp;del');
}

if (isset($_GET['return'])) {
    $doc->ret(__('В тему'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('В тему'), 'theme.php?id=' . $theme['id']);
}

$doc->ret(__('В раздел'), 'topic.php?id=' . $theme['id_topic']);
$doc->ret(__('В категорию'), 'category.php?id=' . $theme['id_category']);
$doc->ret(__('Форум'), './');

if (isset($_GET['id_user'])) {
    $id_user = (int) $_GET['id_user'];
    $ank = new user($id_user);

    if (!$ank->group) {
        $doc->err(__('Нет данных'));
    } else {
        $q = $db->prepare("UPDATE `forum_themes` SET `id_moderator` = ? WHERE `id` = ? LIMIT 1");
        $q->execute(Array($ank->id, $theme['id']));
        $doc->msg(__('Модератор назначен'));
        header('Refresh: 1; ?id=' . $theme['id']);
        exit;
    }
}

if (isset($_GET['del'])) {
    $q = $db->prepare("UPDATE `forum_themes` SET `id_moderator` = ? WHERE `id` = ? LIMIT 1");
    $q->execute(Array(0, $theme['id']));
    $doc->msg(__('Модератор снят'));
    header('Refresh: 1; ?id=' . $theme['id']);
    exit;
}

$pages = new pages($db->query("SELECT COUNT(*) FROM `users` INNER JOIN `forum_messages` ON `forum_messages`.`id_theme` = '" . $theme['id'] . "' AND `forum_messages`.`id_user` = `users`.`id` WHERE `users`.`group` = '1' GROUP BY `users`.`id`")->fetchColumn());
$listing = new listing();

$q = $db->query("SELECT `users`.`id` FROM `users` INNER JOIN `forum_messages` ON `forum_messages`.`id_theme` = '" . $theme['id'] . "' AND `forum_messages`.`id_user` = `users`.`id` WHERE `users`.`group` = '1' GROUP BY `users`.`id` LIMIT " . $pages->limit);
$res = $q->fetchAll();
foreach ($res AS $users) {
    $ank = new user($users['id']);

    $post = $listing->post();
    $post->title = $ank->nick();
    $post->icon($ank->icon());
    if ($ank->id == $theme['id_moderator']) {
        $post->bottom = __('Модератор');
    } else {
        $post->url = '?id=' . $theme['id'] . '&amp;id_user=' . $ank->id;
    }
}
$listing->display(__('Пользователи не найдены'));
$pages->display('?id=' . $theme['id'] . '&amp;');
