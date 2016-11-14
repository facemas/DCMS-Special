<?php

include_once '../sys/inc/start.php';
if (AJAX) {
    $doc = new document_json();
} else {
    $doc = new document();
}
$doc->title = __('Чат');

$pages = new pages($db->query("SELECT COUNT(*) FROM `chat_mini`")->fetchColumn());

$can_write = true;
/** @var $user \user */
if (!$user->is_writeable) {
    $doc->msg(__('Писать запрещено'), 'write_denied');
    $can_write = false;
}

if ($can_write && $pages->this_page == 1) {
    if (isset($_POST['send']) && isset($_POST['message']) && isset($_POST['token']) && $user->group) {
        $message = (string) $_POST['message'];
        $users_in_message = text::nickSearch($message);
        $message = text::input_text($message);

        if (!antiflood::useToken($_POST['token'], 'chat_mini')) {
            // нет токена (обычно, повторная отправка формы)
        } elseif ($dcms->censure && $mat = is_valid::mat($message)) {
            $doc->err(__('Обнаружен мат: %s', $mat));
        } elseif ($message) {
            $user->balls += $dcms->add_balls_chat;
            $res = $db->prepare("INSERT INTO `chat_mini` (`id_user`, `time`, `message`) VALUES (?, ?, ?)");
            $res->execute(Array($user->id, TIME, $message));
            header('Refresh: 1; url=?' . passgen() . '&' . SID);
            $doc->ret(__('Вернуться'), '?' . passgen());
            $doc->msg(__('Сообщение успешно отправлено'));

            if ($doc instanceof document_json) {
                $doc->form_value('message', '');
                $doc->form_value('token', antiflood::getToken('chat_mini'));
            }

            exit;
        } else {
            $doc->err(__('Сообщение пусто'));
        }

        if ($doc instanceof document_json) {
            $doc->form_value('token', antiflood::getToken('chat_mini'));
        }
    }
    if ($user->group) {
        $message_form = '';
        if (isset($_GET['message']) && is_numeric($_GET['message'])) {
            $id_message = (int) $_GET['message'];
            $q = $db->prepare("SELECT * FROM `chat_mini` WHERE `id` = ? LIMIT 1");
            $q->execute(Array($id_message));
            if ($message = $q->fetch()) {
                $ank = new user($message['id_user']);
                if (isset($_GET['reply'])) {
                    $message_form = '@' . $ank->login . ',';
                } elseif (isset($_GET['quote'])) {
                    $message_form = "[quote id_user=\"{$ank->id}\" time=\"{$message['time']}\"]{$message['message']}[/quote]";
                }
            }
        }

        if (!AJAX) {
            $form = new form('?' . passgen());
            $form->refresh_url('?' . passgen());
            $form->setAjaxUrl('?');
            $form->hidden('token', antiflood::getToken('chat_mini'));
            $form->textarea('message', __('Сообщение'), $message_form, false, false);
            $form->button(__('Отправить'), 'send', false, 'tiny ui blue button');
            $form->display();
        }
    }
}

$listing = new ui_components();
$listing->ui_comment = true; //подключаем css comments
$listing->class = $dcms->browser_type == 'full' ? 'minimal comments large listing' : 'comments large listing';

// привязываем форму к листингу, чтобы листинг мог обновиться при отправке формы через AJAX
if (!empty($form)) {
    $listing->setForm($form);
}

$q = $db->query("SELECT * FROM `chat_mini` ORDER BY `id` DESC LIMIT " . $pages->limit);
$after_id = false;

if ($arr = $q->fetchAll()) {
    foreach ($arr AS $message) {
        $ank = new user($message['id_user']);

        $post = $listing->post();
        $post->class = 'comment';
        $post->comments = true;

        $post->id = 'chat_post_' . $message['id'];
        $post->url = 'actions.php?id=' . $message['id'];
        $post->avatar = $ank->getAvatar();
        $post->image_a_class = 'avatar';
        $post->time = misc::timek($message['time']);
        $post->login = $ank->nick();
        $post->content = text::toOutput($message['message']);

        if ($user->group && ($user->id != $ank->id)) {
            $post->action(false, "?message=$message[id]&amp;reply", __('Ответить'));
        }
        if ($user->group) {
            $post->action(false, "?message=$message[id]&amp;quote", __('Цитировать'));
        }
        if ($user->group >= 2) {
            $post->action(false, "message.delete.php?id=$message[id]", __('Удалить'));
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
    $post = new ui_compost(__('Нет результатов'));
    $post->icon('clone');
    $doc->add_post($post);
}

$listing->setAjaxUrl('?page=' . $pages->this_page);
$listing->display(__('Нет результатов'));
$pages->display('?'); // вывод страниц

if ($doc instanceof document_json) {
    $doc->set_pages($pages);
}

if ($user->group >= 3) {
    $doc->opt(__('Очистить чат'), 'message.delete_all.php', false, '<i class="fa fa-trash-o fa-fw"></i>');
}