<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Форум');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора темы'));
    exit;
}
$id_theme = (int) $_GET['id'];
$q = $db->prepare("SELECT `th`.* , `cat`.`name` AS `category_name` , `tp`.`name` AS `topic_name` FROM `forum_themes` AS `th` JOIN `forum_categories` AS `cat` ON `cat`.`id` = `th`.`id_category` JOIN `forum_topics` AS `tp` ON `tp`.`id` = `th`.`id_topic` WHERE `th`.`id` = :id_theme AND `th`.`group_show` <= :gr AND `tp`.`group_show` <= :gr AND `cat`.`group_show` <= :gr");
$q->execute(Array(':id_theme' => $id_theme, ':gr' => current_user::getInstance()->group));
if (!$theme = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Тема не доступна'));
    exit;
}

if ($user->group) {
    $q = $db->prepare("SELECT * FROM `forum_views` WHERE `id_theme` = ? AND `id_user` = ? AND `time` > ?");
    $q->execute(Array($theme['id'], $user->id, DAY_TIME));
    if (!$q->fetch()) {
        // если пользователь сегодня еще не заходил в тему, то добавляем запись
        $res = $db->prepare("INSERT INTO `forum_views` (`id_theme`, `id_user`, `time`) VALUES (?, ?, ?)");
        $res->execute(Array($theme['id'], $user->id, (TIME + 1)));
    } else {
        // если пользователь уже сегодня заходил в тему, то обновляем время у существующей записи
        $res = $db->prepare("UPDATE `forum_views` SET `time` = ? WHERE `id_theme` = ? AND `id_user` = ? ORDER BY `time` DESC LIMIT 1");
        $res->execute(Array((TIME + 1), $theme['id'], $user->id));
    }
}

$doc->title .= ' - ' . $theme['name'];

$doc->keywords[] = $theme['keywords'];

$res = $db->prepare("SELECT COUNT(*) FROM `forum_messages` WHERE `id_theme` = ? AND `group_show` <= ?");
$res->execute(Array($theme['id'], $user->group));
$pages = new pages;
$pages->posts = $res->fetchColumn();


include 'inc/theme.votes.php';

$img_thumb_down = '<span class="thumb_down"><i class="fa fa-thumbs-down"></i></span>';
$img_thumb_up = '<span class="thumb_up"><i class="fa fa-thumbs-up"></i></span>';

$q = $db->prepare("SELECT * FROM `forum_messages` WHERE `id_theme` = ? AND `group_show` <= ? ORDER BY `id` ASC LIMIT " . $pages->limit);
$q->execute(Array($theme['id'], $user->group));
$users_preload = array();
$messages = array();
$msg_ids = array();
while ($message = $q->fetch()) {
    $msg_ids[] = $message['id'];
    $messages[] = $message;
    $users_preload[] = $message['id_user'];
}

new user($users_preload); // предзагрузка данных пользователей одним запросом

$ratings = array();
if ($user->group) {
    $q = $db->prepare("SELECT * FROM `forum_rating` WHERE `id_user` = :id_user AND `id_message` IN (" . implode(',', $msg_ids) . ")");
    $q->execute(array(':id_user' => $user->id));
    $forum_rating_result = $q->fetchAll();
    foreach ($forum_rating_result AS $rating) {
        $ratings[$rating['id_message']] = $rating['rating'];
    }
}

$listing = new ui_components();
$listing->ui_comment = true; //подключаем css comments
$listing->ui_segment = true; //подключаем css segments
$listing->class = $dcms->browser_type == 'full' ? 'segments minimal large comments' : 'segments small comments';


foreach ($messages AS $message) {
    $doc->description = $message['message'];

    $post = $listing->post();
    $post->class = 'segment comment';
    $post->comments = true;

    $post->id = 'message' . $message['id'];
    $ank = new user((int) $message['id_user']);

    if ($user->group) {
        $post->action(false, "message.php?id_message=$message[id]&amp;quote", __('Цитировать')); // цитирование
        $post->action(false, "message.files.php?id=$message[id]" . (isset($_GET['return']) ? "&amp;return=" . urlencode($_GET['return']) : null), __('Вложения')); // цитирование
        //$doc->act(__('Вложения'), 'message.files.php?id=' . $message['id'] . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
    }

    if ($user->group > $ank->group || ($user->id && $user->id == $theme['id_moderator']) || $user->group == groups::max()) {
        if ($theme['group_show'] <= 1) {
            if ($message['group_show'] <= 1) {
                $post->action(false, "message.edit.php?id=$message[id]&amp;return=" . URL . "&amp;act=hide&amp;" . passgen(), __('Скрыть')); // скрытие
            } else {
                $post->action(false, "message.edit.php?id=$message[id]&amp;return=" . URL . "&amp;act=show&amp;" . passgen(), __('Показать')); // показ

                $post->bottom = __('Сообщение скрыто');
            }
        }
        $post->action(false, "message.edit.php?id=$message[id]&amp;return=" . URL, __('Ред')); // редактирование
    } elseif ($user->id == $message['id_user'] && TIME < $message['time'] + 600) {
        // автору сообщения разрешается его редактировать в течении 10 минут
        $post->action(false, "message.edit.php?id=$message[id]&amp;return=" . URL, __('Ред')); // редактирование
    }

    if ($ank->group <= $user->group && $user->id != $ank->id) {
        if ($user->group >= 2) {
            $post->action(false, "/dpanel/user.ban.php?id_ank=$message[id_user]&amp;return=" . URL . "&amp;link=" . urlencode("/forum/message.php?id_message=$message[id]"), __('Бан'));
        } else {
            $post->action(false, "/complaint.php?id=$message[id_user]&amp;return=" . URL . "&amp;link=" . urlencode("/forum/message.php?id_message=$message[id]"), __('Жалоба'));
        }
    }

    $post->url = 'message.php?id_message=' . $message['id'];
    $post->avatar = $ank->getAvatar();
    $post->image_a_class = 'avatar';
    $post->time = misc::timek($message['time']);
    $doc->last_modified = $message['time'];
    $post->login = $ank->nick();
    $post->content = text::for_opis($message['message']);

    if ($message['edit_id_user'] && ($ank->group < $user->group || $ank->id == $user->id)) {
        $ank_edit = new user($message['edit_id_user']);
        $post->action(false, "message.history.php?id=" . $message['id'] . "&amp;return=" . URL, __('История'));

        $post->bottom .= "<small style='color:grey'>$ank_edit->login " . ($ank_edit->sex ? __('обновил') : __('обновила')) . " " . misc::timek($message['edit_time']) . " (" . $message['edit_count'] . ")</small><br />";
    }

    if ($user->group && $user->id != $ank->id) {
        $my_rating = array_key_exists($message['id'], $ratings) ? $ratings[$message['id']] : 0;
        if ($my_rating === 0 && $user->balls - $dcms->forum_rating_down_balls >= 0) {
            $post->action(false, 'message.rating.php?id=' . $message['id'] . '&amp;change=down&amp;return=' . URL . urlencode('#' . $post->id), $img_thumb_down); // не нравится
        }


        if ($my_rating === 0) {
            $post->bottom .= ' <small>' . __('%s / %s', '<span class="rating_down"><i class="fa fa-thumbs-o-down fa-fw"></i> ' . $message['rating_down'] . '</span>', '<span class="rating_up"><i class="fa fa-thumbs-o-up fa-fw"></i> ' . $message['rating_up'] . '</span>') . '</small> ';
        } else {

            $rat = ($my_rating == 1 ? ' <span class="rating_up">' . __('Вам нравится') . '</span> ' : ' <span class="rating_down">' . __('Вам не нравится') . '</span> ');
            $post->bottom .= ' <small>' . __('%s / %s / %s', '<span class="rating_down"><i class="fa fa-thumbs-down fa-fw"></i> ' . $message['rating_down'] . '</span>', '<span class="rating_up"><i class="fa fa-thumbs-up fa-fw"></i> ' . $message['rating_up'] . '</span>', $rat) . '</small> ';
        }

        if ($my_rating === 0) {
            $post->action(false, 'message.rating.php?id=' . $message['id'] . '&amp;change=up&amp;return=' . URL . urlencode('#' . $post->id), $img_thumb_up); // нравится
        }
    } else {
        $post->bottom .= ' <small>' . __('%s / %s', '<span class="rating_down"><i class="fa fa-thumbs-down fa-fw"></i> ' . $message['rating_down'] . '</span>', '<span class="rating_up"><i class="fa fa-thumbs-up fa-fw"></i> ' . $message['rating_up'] . '</span>') . '</small> ';
    }

    $post_dir_path = H . '/sys/files/.forum/' . $theme['id'] . '/' . $message['id'];
    if (@is_dir($post_dir_path)) {
        $listing_files = new listing();
        $listing_files->ui_list = true;
        $listing_files->class = 'list';
        $dir = new files($post_dir_path);
        $content = $dir->getList('time_add:asc');
        $files = &$content['files'];
        $count = count($files);
        for ($i = 0; $i < $count; $i++) {
            $file = $listing_files->post();
            $file->image = $files[$i]->image();
            $file->image_class = 'ui large image';
            $file->title = text::toValue($files[$i]->runame);
            $file->url = "/files" . $files[$i]->getPath() . ".htm?order=time_add:asc";
            $file->content[] = $files[$i]->properties;
            $file->icon($files[$i]->icon());
        }
        if ($count) {
            $post->bottom .= $listing_files->fetch();
        }
    }
}

$listing->display(__('Сообщения отсутствуют'));

$pages->display('theme.php?id=' . $theme['id'] . '&amp;'); // вывод страниц

$can_write = true;
if (!$user->is_writeable) {
    $doc->msg(__('Писать запрещено'), 'write_denied');
    $can_write = false;
}

# Доступ к написанию сообщений
if ($can_write && $user->group) { #
    if (isset($_POST['message']) && $user->group) { #
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

if ($theme['group_write'] <= $user->group) {
    $doc->opt(__('Написать сообщение'), 'message.new.php?id_theme=' . $theme['id'] . "&amp;return=" . URL, false, '<i class="fa fa-pencil fa-fw"></i>');
}

if ($user->group >= 2 || $theme['group_edit'] <= $user->group || ($user->id && $user->id == $theme['id_moderator'])) {
    $doc->opt(__('Действия'), 'theme.actions.php?id=' . $theme['id'], false, '<i class="fa fa-location-arrow fa-fw"></i>');
}

$doc->act($theme['topic_name'], 'topic.php?id=' . $theme['id_topic']);
$doc->act($theme['category_name'], 'category.php?id=' . $theme['id_category']);
$doc->ret(__('Форум'), './');
