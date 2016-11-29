<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Редактирование сообщения');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $doc->toReturn();
    $doc->err(__('Ошибка выбора сообщения'));
    exit;
}

$id_message = (int) $_GET['id'];
$q = $db->prepare("SELECT `forum_messages`.*, `forum_themes`.`id_moderator` AS `id_moderator` FROM `forum_messages` INNER JOIN `forum_themes` ON `forum_themes`.`id` = `forum_messages`.`id_theme` WHERE `forum_messages`.`id` = ? LIMIT 1");
$q->execute(Array($id_message));

if (!$message = $q->fetch()) {
    $doc->toReturn();
    $doc->err(__('Сообщение не найдено'));

    exit;
}
$autor = new user((int) $message['id_user']);

$access_edit = false;
$edit_time = $message['time'] - TIME + 600;

if ($user->group > $autor->group || $user->group == groups::max() || $user->id == $message['id_moderator']) {
    $access_edit = true;
} elseif ($user->id == $autor->id && $edit_time > 0) {
    $access_edit = true;
    $doc->msg(__('Для изменения сообщения осталось %d сек', $edit_time));
}

if (!$access_edit) {
    $doc->toReturn();
    $doc->err(__('Сообщение не доступно для редактирования'));
    exit;
}


$doc->title = __('Сообщение от "%s" - редактирование', $autor->login);

if (isset($_GET['act']) && $_GET['act'] == 'hide') {
    $doc->toReturn(new url('theme.php', array('id' => $message['id_theme'])));
    $res = $db->prepare("UPDATE `forum_messages` SET `group_show` = '2' WHERE `id` = ? LIMIT 1");
    $res->execute(Array($message['id']));
    $doc->msg(__('Сообщение успешно скрыто'));
    exit;
}

if (isset($_GET['act']) && $_GET['act'] == 'show') {
    $doc->toReturn(new url('theme.php', array('id' => $message['id_theme'])));
    $res = $db->prepare("UPDATE `forum_messages` SET `group_show` = '0' WHERE `id` = ? LIMIT 1");
    $res->execute(Array($message['id']));
    $doc->msg(__('Сообщение будет отображаться'));
    exit;
}

if (isset($_POST['message'])) {
    $message_new = text::input_text($_POST['message']);

    if ($message_new == $message['message']) {
        $doc->err(__('Изменения не обнаружены'));
    } elseif ($dcms->censure && $mat = is_valid::mat($message_new)) {
        $doc->err(__('Обнаружен мат: %', $mat));
    } elseif ($message_new) {
        $doc->toReturn(new url('theme.php', array('id' => $message['id_theme'])));

        $res = $db->prepare("INSERT INTO `forum_history` (`id_message`, `id_user`, `time`, `message`) VALUES (?,?,?,?)");
        $res->execute(Array(
            $message['id'],
            ($message['edit_id_user'] ? $message['edit_id_user'] : $message['id_user']),
            ($message['edit_time'] ? $message['edit_time'] : $message['time']),
            $message['message']
        ));
        $res = $db->prepare("UPDATE `forum_messages` SET `message` = ?, `edit_count` = `edit_count` + 1, `edit_id_user` = ?, `edit_time` = ? WHERE `id` = ? LIMIT 1");
        $res->execute(Array($message_new, $user->id, TIME, $message['id']));
        $doc->msg(__('Сообщение успешно изменено'));
        exit;
    } else {
        $doc->err(__('Нельзя оставить пустое сообщение'));
    }
}

$form = new form(new url());
$form->textarea('message', __('Редактирование сообщения'), $message['message']);
$form->button(__('Сохранить'));
$form->display();

$doc->act(__('Вложения'), 'message.files.php?id=' . $message['id'] . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));

if (isset($_GET['return'])) {
    $doc->ret(__('В тему'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('В тему'), 'theme.php?id=' . $message['id_theme']);
}