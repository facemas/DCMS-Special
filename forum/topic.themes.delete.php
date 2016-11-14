<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Удаление тем');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора раздера'));
    exit;
}
$id_topic = (int)$_GET['id'];

$q = $db->prepare("SELECT * FROM `forum_topics` WHERE `id` = ? AND `group_edit` <= ?");
$q->execute(Array($id_topic, $user->group));
if (!$topic = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Раздел не доступен для редактирования'));
    exit;
}


$doc->title .= ' - ' . $topic['name'];

switch (@$_GET['show']) {
    case 'all':
        $show = 'all';
        break;
    default:
        $show = 'part';
        break;
}

if (isset($_POST['delete'])) {
    $deleted = 0;

    $q = $db->prepare("SELECT * FROM `forum_themes` WHERE `id` = ? AND `group_edit` <= ? LIMIT 1");
    $res_del1 = $db->prepare("DELETE FROM `forum_themes` WHERE `id` = ? LIMIT 1");
    $res_del2 = $db->prepare("DELETE
FROM `forum_messages`, `forum_history`
USING `forum_messages`
LEFT JOIN `forum_history` ON `forum_history`.`id_message` = `forum_messages`.`id`
WHERE `forum_messages`.`id_theme` = ?");
    $res_del3 = $db->prepare("DELETE FROM `forum_vote` WHERE `id_theme` = ?");
    $res_del4 = $db->prepare("DELETE FROM `forum_vote_votes` WHERE `id_theme` = ?");
    $res_del5 = $db->prepare("DELETE FROM `forum_views` WHERE `id_theme` = ?");
    foreach ($_POST as $key => $value) {
        if ($value && preg_match('#^theme([0-9]+)$#ui', $key, $n)) {
            if (function_exists('set_time_limit'))
                set_time_limit(30);

            $q->execute(Array($n[1], $user->group));
            if (!$theme = $q->fetch()) {
                continue;
            }

            $res_del1->execute(Array($theme['id']));
            $res_del2->execute(Array($theme['id']));
            $res_del3->execute(Array($theme['id']));
            $res_del4->execute(Array($theme['id']));
            $res_del5->execute(Array($theme['id']));

            $dir = new files(FILES . '/.forum/' . $theme['id']);
            $dir->delete();
            unset($dir);

            $deleted++;
        }
    }

    $dcms->log('Форум', 'Удаление ' . $deleted . ' тем' . misc::number($deleted, 'ы', '', '') . ' из раздела [url=/forum/topic.php?id=' . $topic['id'] . ']' . $topic['name'] . '[/url]');
    $doc->msg(__('Успешно удален' . misc::number($deleted, 'а', 'ы', 'о') . ' %d тем' . misc::number($deleted, 'а', 'ы', ''), $deleted));
}

$doc->tab(__('Все'), "?id=$topic[id]&amp;show=all", $show == 'all');
$doc->tab(__('Постранично'), "?id=$topic[id]&amp;show=part", $show == 'part');

$listing = new listing();
if ($show == 'part') {
    $res = $db->prepare("SELECT COUNT(*) FROM `forum_themes` WHERE `id_topic` = ? AND `group_show` <= ?");
    $res->execute(Array($topic['id'], $user->group));
    $pages = new pages;
    $pages->posts = $res->fetchColumn();
    $q = $db->prepare("SELECT * FROM `forum_themes`  WHERE `id_topic` = ? AND `group_show` <= ? ORDER BY `time_last` DESC LIMIT " . $pages->limit);
    $q->execute(Array($topic['id'], $user->group));
} else {
    $q = $db->prepare("SELECT * FROM `forum_themes`  WHERE `id_topic` = ? AND `group_show` <= ? ORDER BY `time_last` DESC");
    $q->execute(Array($topic['id'], $user->group));
}
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $theme) {
        $ch = $listing->checkbox();
        $ch->name = 'theme' . $theme['id'];
        $ch->title = text::toValue($theme['name']);

        $autor = new user($theme['id_autor']);
        $last_msg = new user($theme['id_last']);

        $ch->content = ($autor->id != $last_msg->id ? $autor->nick . '/' . $last_msg->nick : $autor->nick) . ' (' . misc::when($theme['time_last']) . ')';
    }
}

$form = new form('?id=' . $topic['id']);
$form->html($listing->fetch(__('Темы отсутствуют')));
$form->button(__('Удалить выделенные темы'), 'delete');
$form->display();

if ($show == 'part')
    $pages->display('?id=' . $topic['id'] . '&amp;show=part&amp;');

$doc->ret(__('В раздел'), 'topic.php?id=' . $topic['id']);
$doc->ret(__('В категорию'), 'category.php?id=' . $topic['id_category']);
$doc->ret(__('Форум'), './');