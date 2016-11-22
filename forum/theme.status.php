<?php

include_once '../sys/inc/start.php';
$doc = new document(2);
$doc->title = __('Форум');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора темы'));
    exit;
}

$id_theme = (int) $_GET['id'];

$q = $db->prepare("SELECT `forum_themes`.* , `forum_categories`.`name` AS `category_name`, `forum_topics`.`name` AS `topic_name`, `forum_topics`.`group_write` AS `topic_group_write` FROM `forum_themes` LEFT JOIN `forum_categories` ON `forum_categories`.`id` = `forum_themes`.`id_category` LEFT JOIN `forum_topics` ON `forum_topics`.`id` = `forum_themes`.`id_topic` WHERE `forum_themes`.`id` = ? AND `forum_themes`.`group_show` <= ? AND `forum_topics`.`group_show` <= ? AND `forum_categories`.`group_show` <= ?");
$q->execute(Array($id_theme, $user->group, $user->group, $user->group));
if (!$theme = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Тема не доступна'));
    exit;
}

$doc->ret(__('Действия'), 'theme.actions.php?id=' . $theme['id']);
$doc->ret(__('Вернуться в тему'), 'theme.php?id=' . $theme['id']);
$doc->ret(empty($theme['topic_name']) ? __('В раздел') : $theme['topic_name'], 'topic.php?id=' . $theme['id_topic']);
$doc->ret(empty($theme['category_name']) ? __('В категорию') : $theme['category_name'], 'category.php?id=' . $theme['id_category']);
$doc->ret(__('Форум'), './');

$group_write_open = $theme['topic_group_write'];
$group_write_close = $theme['topic_group_write'] + 1;

$is_open = $theme['group_write'] <= $group_write_open;

$doc->title = $is_open ? __('Закрытие темы %s', $theme['name']) : __('Открытие темы %s', $theme['name']);

if (!empty($_POST['open'])) {
    if ($is_open) {
        $doc->msg(__('Тема уже открыта для обсуждения'));
    } else {
        $theme['group_write'] = $group_write_open;
        $res = $db->prepare("UPDATE `forum_themes` SET `group_write` = ? WHERE `id` = ? LIMIT 1");
        $res->execute(Array($theme['group_write'], $theme['id']));
        $message = __('%s открыл' . ($user->sex ? '' : 'а') . ' тему для обсуждения', '[user]' . $user->id . '[/user]');

        if ($reason = text::input_text($_POST['reason'])) {
            $message .= "\n" . __('Причина: %s', $reason);
        }
        $dcms->log('Форум', 'Открытие темы [url=/forum/theme.php?id=' . $theme['id'] . ']' . $theme['name'] . '[/url]' . ($reason ? "\nПричина: $reason" : ''));

        $res = $db->prepare("INSERT INTO `forum_messages` (`id_category`, `id_topic`, `id_theme`, `id_user`, `time`, `message`, `group_show`, `group_edit`) VALUES (?,?,?,'0',?,?,?,?)");
        $res->execute(Array($theme['id_category'], $theme['id_topic'], $theme['id'], TIME, $message, $theme['group_show'], $theme['group_edit']));
        $doc->msg(__('Тема успешно открыта для обсуждения'));
        exit;
    }
}

if (!empty($_POST['close'])) {
    if (!$is_open) {
        $doc->msg(__('Тема уже закрыта для обсуждения'));
    } else {
        $theme['group_write'] = $group_write_close;
        $res = $db->prepare("UPDATE `forum_themes` SET `group_write` = ? WHERE `id` = ? LIMIT 1");
        $res->execute(Array($theme['group_write'], $theme['id']));
        $message = __('%s закрыл' . ($user->sex ? '' : 'а') . ' тему для обсуждения', '[user]' . $user->id . '[/user]');

        if ($reason = text::input_text($_POST['reason'])) {
            $message .= "\n" . __('Причина: %s', $reason);
        }

        $dcms->log('Форум', 'Закрытие темы [url=/theme.php?id=' . $theme['id'] . ']' . $theme['name'] . '[/url]' . ($reason ? "\nПричина: $reason" : ''));
        $res = $db->prepare("INSERT INTO `forum_messages` (`id_category`, `id_topic`, `id_theme`, `id_user`, `time`, `message`, `group_show`, `group_edit`) VALUES (?,?,?,'0',?,?,?,?)");
        $res->execute(Array($theme['id_category'], $theme['id_topic'], $theme['id'], TIME, $message, $theme['group_show'], $theme['group_edit']));
        $doc->msg(__('Тема успешно закрыта для обсуждения'));
        exit;
    }
}

$form = new form(new url());
$form->textarea('reason', $is_open ? __('Причина закрытия') : __('Причина открытия'));

if ($is_open) {
    $form->block('<input type="submit" name="close" value="' . __('Закрыть для обсуждения') . '" class="tiny ui blue button" />');
} else {
    $form->block('<input type="submit" name="open" value="' . __('Открыть для обсуждения') . '" class="tiny ui blue button" />');
}

$form->display();
?>
