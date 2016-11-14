<?php

include_once '../sys/inc/start.php';

if (AJAX) {
    $doc = new document_json(1);
} else {
    $doc = new document(1);
}

$doc->title = __('Фото профиля : Комментарии');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора фото профиля'));
    exit;
}
$id_st = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `users` WHERE `id` = ?");
$q->execute(Array($id_st));
if (!$ava = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Пользователь не найден'));
    exit;
}


$avtor = new user((int) $ava['id']);

if ($avtor->avatar == 0) {
    $doc->err(__('Фото профиля не найдено'));
    exit;
}

$listing = new listing();
$post = $listing->post();
$post->title = $avtor->nick();

$post->time = '<i class="fa fa-heart fa-fw"></i> ' . $db->query("SELECT COUNT(*) FROM `avatar_like` WHERE `id_avatar` = '$avtor->id' ")->fetchColumn();
$post->post = "<img class='photo' src='" . $avtor->getAvatar($dcms->browser_type == 'full' ? '320' : '220') . "'/>";
$listing->display();

if (isset($_GET['comment']) && $user->id == $avtor->id) {
    $id_message = (int) $_GET['comment'];
    $q = $db->prepare("SELECT * FROM `avatar_komm` WHERE `id` = ? LIMIT 1");
    $q->execute(Array($id_message));
    if (!$message = $q->fetch()) {
        $doc->err(__('Комментарий не найден'));
    } else {


        $q = $db->prepare("DELETE FROM `avatar_komm` WHERE `id` = ? LIMIT 1");
        $q->execute(Array($id_message));
        $doc->msg(__('Комментарий успешно удален'));
        header('Refresh: 1; url=?id=' . $ava['id'] . '&' . passgen() . '&' . SID);
    }
}



$ank = (empty($_GET ['id'])) ? $user : new user((int) $_GET ['id']);
$can_write = true;
if (!$user->is_writeable) {
    $doc->msg(__('Писать запрещено'), 'write_denied');
    $can_write = false;
}


$pages = new pages($db->query("SELECT COUNT(*) FROM  `avatar_komm` WHERE `id_avatar` = '" . $ava['id'] . "'")->fetchColumn());

if ($can_write && $pages->this_page == 1) {
    if (isset($_POST['send']) && isset($_POST['message']) && isset($_POST['token']) && $user->group) {
        $message = (string) $_POST['message'];
        $users_in_message = text::nickSearch($message);
        $message = text::input_text($message);

        if (!antiflood::useToken($_POST['token'], 'avatar_komm')) {
            // нет токена (обычно, повторная отправка формы)
        } elseif ($dcms->censure && $mat = is_valid::mat($message)) {
            $doc->err(__('Обнаружен мат: %s', $mat));
        } elseif ($message) {
            //$user->balls += $dcms->add_balls_chat ;
            if ($users_in_message) {
                for ($i = 0; $i < count($users_in_message) && $i < 20; $i++) {
                    $user_id_in_message = $users_in_message[$i];
                    if ($user_id_in_message == $user->id) {
                        continue;
                    }
                    $ank_in_message = new user($user_id_in_message);
                    if ($ank_in_message->notice_mention) {
                        $ank_in_message->not("" . ($user->sex ? 'упомянул' : 'упомянула') . " вас в комментарии к [url=/avatar.comments.php?id=" . $ava['id'] . "]аватару[/url]", $user->id);
                    }
                }
            } elseif ($avtor->id != $user->id) {
                $avtor->not("" . ($user->sex ? 'оставил' : 'оставила') . " комментарий к вашему [url=/avatar.comments.php?id=" . $ava['id'] . "]аватару[/url]", $user->id);
            }


            $res = $db->prepare("INSERT INTO `avatar_komm` (`id_user`, `time`, `msg`, `id_avatar`) VALUES (?,?, ?, ?)");
            $res->execute(Array($user->id, TIME, $message, intval($ava['id'])));


            header('Refresh: 1; url=?id=' . $ava['id'] . '&' . passgen() . '&' . SID);
            $doc->ret(__('Вернуться'), '?' . passgen());
            $doc->msg(__('Сообщение успешно отправлено'));

            if ($doc instanceof document_json) {
                $doc->form_value('message', '');
                $doc->form_value('token', antiflood::getToken('avatar_komm'));
            }

            exit;
        } else {
            $doc->err(__('Сообщение пусто'));
        }

        if ($doc instanceof document_json) {
            $doc->form_value('token', antiflood::getToken('avatar_komm'));
        }
    }

    if ($user->group) {
        $message_form = '';
        if (isset($_GET['message']) && is_numeric($_GET['message'])) {
            $id_message = (int) $_GET['message'];
            $q = $db->prepare("SELECT * FROM `avatar_komm` WHERE `id` = ? LIMIT 1");
            $q->execute(Array($id_message));
            if ($message = $q->fetch()) {
                $ank = new user($message['id_user']);
                if (isset($_GET['reply'])) {
                    $message_form = '@' . $ank->login . ', ';
                } elseif (isset($_GET['quote'])) {
                    $message_form = "[quote id_user=\"{$ank->id}\" time=\"{$message['time']}\"]{$message['msg']}[/quote] ";
                }
            }
        }

        if (!AJAX) {
            $form = new form('?id=' . $ava['id'] . '&amp;' . passgen());
            $form->refresh_url('?id=' . $ava['id'] . '&amp;' . passgen());
            $form->setAjaxUrl('?id=' . $ava['id'] . '&amp;' . passgen());
            $form->hidden('token', antiflood::getToken('avatar_komm'));
            $form->textarea('message', __('Сообщение'), $message_form, true);
            $form->button(__('Отправить'), 'send', false);
            $form->display();
        }
    }
}

$listing = new listing();

if (!empty($form)) {
    $listing->setForm($form);
}

$q = $db->query("SELECT * FROM `avatar_komm` WHERE `id_avatar` = '" . $ava['id'] . "' ORDER BY `id` DESC LIMIT " . $pages->limit);
$after_id = false;

if ($arr = $q->fetchAll()) {
    foreach ($arr AS $message) {
        $ank = new user($message['id_user']);
        $post = $listing->post();
        $post->id = 'avatar_komm_' . $message['id'];
        $post->url = '/profile.view.php?id=' . $ank->id;
        $post->time = misc::timek($message['time']);
        $post->title = $ank->nick();
        $post->image = $ank->getAvatar();
        $post->post = text::toOutput($message['msg']);
        if ($user->group) {
            $post->action('pencil', '?id=' . $ava['id'] . '&amp;message=' . $message['id'] . '&amp;reply');
            $post->action('quote-left', '?id=' . $ava['id'] . '&amp;message=' . $message['id'] . '&amp;quote');
        }

        if ($user->group >= 2 || $user->id == $avtor->id) {
            $post->action('trash-o', '?id=' . $ava['id'] . '&amp;comment=' . $message['id'] . '&amp;page=' . $pages->this_page);
        }


        if (!$doc->last_modified) {
            $doc->last_modified = $message['time'];
        }

        if ($doc instanceof document_json) {
            $doc->add_post($post, $after_id);
        }

        $after_id = $post->id;
    }
}

if ($doc instanceof document_json && !$arr) {
    $post = new listing_post(__('Нет результатов'));
    $post->icon('clone');
    $doc->add_post($post);
}

$listing->setAjaxUrl('?id=' . $ava['id'] . '&amp;page=' . $pages->this_page);
$listing->display(__('Нет результатов'));
$pages->display('?id=' . $ava['id'] . '&amp;'); // вывод страниц

if ($doc instanceof document_json) {
    $doc->set_pages($pages);
}

$doc->ret(__('Вернуться в анкету'), '/profile.view.php?id=' . $avtor->id);
