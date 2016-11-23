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

if (isset($_POST['save'])) {

    if (isset($_POST['topic'])) {
        $topic = (int) $_POST['topic'];

        $q = $db->prepare("SELECT `ft`.*, `fc`.`name` AS `category_name` FROM `forum_topics` AS `ft` LEFT JOIN `forum_categories` AS `fc` ON `ft`.`id_category` = `fc`.`id` WHERE `ft`.`id` = ? AND `ft`.`group_show` <= ? AND `ft`.`group_write` <= ? LIMIT 1");
        $q->execute(Array($topic, $user->group, $user->group));

        if ($topic != $theme['id_topic'] AND $topic = $q->fetch()) {

            $theme['id_topic_old'] = $theme['id_topic'];
            $theme['id_topic'] = $topic['id'];
            $theme['topic_name_old'] = $theme['topic_name'];
            $theme['topic_name'] = $topic['name'];

            $theme['id_category_old'] = $theme['id_category'];
            $theme['id_category'] = $topic['id_category'];
            $theme['category_name_old'] = $theme['category_name'];
            $theme['category_name'] = $topic['category_name'];

            $group_write_open = $theme['topic_group_write'];

            if ($theme['group_write'] <= $group_write_open) {
                // тема была открыта
                // назначаются теже права на запись что и на создание тем в новом разделе
                $theme['group_write'] = $topic['group_write'];
            } else {
                // тема была закрыта
                // тема по прежнему закрыта, но в соответствии с правами раздела
                $theme['group_write'] = $topic['group_write'] + 1;
            }

            $res = $db->prepare("UPDATE `forum_themes` SET `id_topic` = ?, `id_category` = ?, `group_show` = ?, `group_write` = ? WHERE `id` = ? LIMIT 1");
            $res->execute(Array($theme['id_topic'], $theme['id_category'], $topic['group_show'], $theme['group_write'], $theme['id']));
            $res = $db->prepare("UPDATE `forum_messages` SET `id_topic` = ? WHERE `id_theme` = ?");
            $res->execute(Array($theme['id_topic'], $theme['id']));
            $theme_dir = new files(FILES . '/.forum/' . $theme['id']);
            $theme_dir->setGroupShowRecurse($topic['group_show']); // данный параметр необходимо применять рекурсивно

            $message = __('%s переместил' . ($user->sex ? '' : 'а') . ' тему из раздела %s в раздел %s', '[user]' . $user->id . '[/user]', '[url=/forum/category.php?id=' . $theme['id_category_old'] . ']' . $theme['category_name_old'] . '[/url]/[url=/forum/topic.php?id=' . $theme['id_topic_old'] . ']' . $theme['topic_name_old'] . '[/url]', '[url=/forum/category.php?id=' . $theme['id_category'] . ']' . $theme['category_name'] . '[/url]/[url=/forum/topic.php?id=' . $theme['id_topic'] . ']' . $theme['topic_name'] . '[/url]');
            if ($reason = text::input_text($_POST['reason'])) {
                $message .= "\n" . __('Причина: %s', $reason);
            }

            $dcms->log('Форум', __('Перемещение темы %s из раздела %s в раздел %s', '[url=/forum/theme.php?id=' . $theme['id'] . ']' . $theme['name'] . '[/url]', '[url=/forum/category.php?id=' . $theme['id_category_old'] . ']' . $theme['category_name_old'] . '[/url]/[url=/forum/topic.php?id=' . $theme['id_topic_old'] . ']' . $theme['topic_name_old'] . '[/url]', '[url=/forum/category.php?id=' . $theme['id_category'] . ']' . $theme['category_name'] . '[/url]/[url=/forum/topic.php?id=' . $theme['id_topic'] . ']' . $theme['topic_name'] . '[/url]' . ($reason ? "\nПричина: $reason" : '')));

            $res = $db->prepare("INSERT INTO `forum_messages` (`id_category`, `id_topic`, `id_theme`, `id_user`, `time`, `message`, `group_show`, `group_edit`) VALUES (?,?,?,'0',?,?,?,?)");
            $res->execute(Array($theme['id_category'], $theme['id_topic'], $theme['id'], TIME, $message, $theme['group_show'], $theme['group_edit']));
            $doc->msg(__('Тема успешно перемещена'));
        }
    }
}

$doc->title = __('Перемещение темы %s', $theme['name']);

$form = new form(new url());
$options = array();
$q = $db->prepare("SELECT `id`,`name` FROM `forum_categories` WHERE `group_show` <= ? ORDER BY `position` ASC");
$q->execute(Array($user->group));
$q2 = $db->prepare("SELECT `id`,`name` FROM `forum_topics` WHERE `id_category` = ? AND `group_show` <= ? AND `group_write` <= ? ORDER BY `time_last` DESC");
while ($category = $q->fetch()) {
    $options[] = array($category['name'], 'groupstart' => 1);
    $q2->execute(Array($category['id'], $user->group, $user->group));
    while ($topic = $q2->fetch())
        $options[] = array($topic['id'], $topic['name'], $topic['id'] == $theme['id_topic']);
    $options[] = array('groupend' => 1);
}
$form->select('topic', __('Раздел'), $options);
$form->textarea('reason', __('Причина перемещения темы'));
$form->button(__('Сохранить'), 'save');

$form->display();

$doc->ret(__('Действия'), 'theme.actions.php?id=' . $theme['id']);
$doc->ret(__('Вернуться в тему'), 'theme.php?id=' . $theme['id']);
$doc->ret(empty($theme['topic_name']) ? __('В раздел') : $theme['topic_name'], 'topic.php?id=' . $theme['id_topic']);
$doc->ret(empty($theme['category_name']) ? __('В категорию') : $theme['category_name'], 'category.php?id=' . $theme['id_category']);
$doc->ret(__('Форум'), './');
