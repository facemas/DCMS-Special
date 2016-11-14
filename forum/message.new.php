<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Новое сообщение');

if (!isset($_GET['id_theme']) || !is_numeric($_GET['id_theme'])) {
    $doc->toReturn();
    $doc->err(__('Ошибка выбора темы'));
    exit;
}

$id_theme = (int) $_GET['id_theme'];

$q = $db->prepare("SELECT * FROM `forum_themes` WHERE `id` = ? AND `group_write` <= ? LIMIT 1");
$q->execute(Array($id_theme, $user->group));

if (!$theme = $q->fetch()) {
    $doc->toReturn();
    $doc->err(__('В выбранную тему писать нельзя'));
    exit;
}


$doc->title = $theme['name'] . ' - ' . __('Новое сообщение');

$can_write = true;
if (!$user->is_writeable) {
    $doc->msg(__('Писать запрещено'), 'write_denied');
    $can_write = false;
}

if ($can_write) {

    if (isset($_POST['message'])) {
        $message = (string) $_POST['message'];
        $users_in_message = text::nickSearch($message);
        $message = text::input_text($message);

        $af = & $_SESSION['antiflood']['forummessage'][$id_theme][$message]; // защита от дублирования сообщений в теме

        if ($dcms->censure && $mat = is_valid::mat($message)) {
            $doc->err(__('Обнаружен мат: %', $mat));
        } elseif (!empty($af) && $af > TIME - 600 || $theme['id_last'] == $user->id && $theme['time_last'] > TIME - 10) {
            $doc->toReturn(new url('theme.php', array('id' => $theme['id'], 'page' => 'end')));
            $doc->ret(__('В тему'), 'theme.php?id=' . $theme['id'] . '&amp;page=end');
            $doc->err(__('Сообщение уже отправлено или вы пытаетесь ответить сами себе'));
            exit;
        } elseif ($dcms->forum_message_captcha && $user->group < 2 && (empty($_POST['captcha']) || empty($_POST['captcha_session']) || !captcha::check($_POST['captcha'], $_POST['captcha_session']))) {
            $doc->err(__('Проверочное число введено неверно'));
        } elseif ($message) {
            $user->balls += $dcms->add_balls_message_forum;
            $af = TIME;

            $post_update = false;

            $q = $db->prepare("SELECT * FROM `forum_messages` WHERE `id_theme` = ? ORDER BY `id` DESC LIMIT 1");
            $q->execute(Array($theme['id']));
            if ($last_post = $q->fetch()) {
                if ($last_post['id_user'] == $user->id && $last_post['time'] > TIME - 7200) {
                    $post_update = true;
                    $id_message = $last_post['id'];
                }
            }

            if ($post_update && !isset($_POST['add_file'])) {
                $message = $last_post['message'] . "\n\n[small]Через " . misc::when(TIME - $theme['time_last'] + TIME) . ":[/small]\n" . $message;

                $res = $db->prepare("UPDATE `forum_messages` SET `message` = ? WHERE `id_theme` = ? AND `id_user` = ? ORDER BY `id` DESC LIMIT 1");
                $res->execute(Array($message, $theme['id'], $user->id));
            } else {
                $res = $db->prepare("INSERT INTO `forum_messages` (`id_category`, `id_topic`, `id_theme`, `id_user`, `time`, `message`, `group_show`, `group_edit`) VALUES (?,?,?,?,?,?,?,?)");
                $res->execute(Array($theme['id_category'], $theme['id_topic'], $theme['id'], $user->id, TIME, $message, $theme['group_show'], $theme['group_edit']));

                $id_message = $db->lastInsertId();
            }
            if (isset($_POST['add_file'])) {
                $doc->toReturn(new url('message.files.php', array('id' => $id_message,
                    'return' => new url('theme.php', array(
                        'id' => $theme['id'],
                        'page' => 'end')
                ))));
                $doc->opt(__('Добавить файлы'), 'message.files.php?id=' . $id_message . '&amp;return=' . urlencode('theme.php?id=' . $theme['id'] . '&page=end'));
            } else {
                $doc->toReturn(new url('theme.php', array(
                    'id' => $theme['id'],
                    'page' => 'end'
                )));
                $doc->ret(__('В тему'), 'theme.php?id=' . $theme['id'] . '&amp;page=end');
            }

            if ($users_in_message) {
                for ($i = 0; $i < count($users_in_message) && $i < 20; $i++) {
                    $user_id_in_message = $users_in_message[$i];
                    if ($user_id_in_message == $user->id) {
                        continue;
                    }
                    $ank_in_message = new user($user_id_in_message);
                    if ($ank_in_message->notice_mention) {
                        $res = $db->prepare("SELECT COUNT(*) FROM `forum_messages` WHERE `id_theme` = ? AND `group_show` <= ?");
                        $res->execute(Array($theme['id'], $ank_in_message->group));
                        $count_posts_for_user = $res->fetchColumn();
                        $ank_in_message->mess("[user]{$user->id}[/user] упомянул" . ($user->sex ? '' : 'а') . " о Вас на форуме в [url=/forum/message.php?id_message={$id_message}]сообщении[/url] в теме [url=/forum/theme.php?id={$theme['id']}&postnum={$count_posts_for_user}#message{$id_message}]{$theme['name']}[/url]");
                    }
                }
            }

            $doc->msg(__('Сообщение успешно отправлено'));
            $res = $db->prepare("UPDATE `forum_themes` SET `time_last` = ?, `id_last` = ? WHERE `id` = ? LIMIT 1");
            $res->execute(Array(TIME, $user->id, $theme['id']));
            exit;
        } else {
            $doc->err(__('Сообщение пусто'));
        }
    }

    $form = new form(new url());
    $form->textarea('message', __('Сообщение'));
    $form->checkbox('add_file', __('Добавить файл'));
    $form->block('<br />');

    if ($dcms->forum_message_captcha && $user->group < 2) {
        $form->captcha();
    }

    $form->button(__('Отправить'));
    $form->display();
}

if (isset($_GET['return'])) {
    $doc->ret(__('В тему'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('В тему'), 'theme.php?id=' . $theme['id']);
}
