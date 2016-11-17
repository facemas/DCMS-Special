<?php

include_once '../sys/inc/start.php';

if (AJAX) {
    $doc = new document_json(1);
} else {
    $doc = new document(1);
}

$doc->title = __('Моя почта');
$doc->head = 'mail';

if (isset($_GET['id'])) {
    $id_kont = (int) $_GET ['id'];
    $ank = new user($id_kont);
    $res = $db->prepare("SELECT COUNT(*) FROM `mail` WHERE `id_user` = ? AND `id_sender` = ?");
    $res->execute(Array($user->id, $id_kont));

    if (!$ank->group && !$res->fetch()) {
        $doc->err(__('Пользователь не найден'));
        $doc->ret(__('К почте'), '?');
        exit;
    }

    $can_write = true;
    if (!$user->is_writeable) {
        $doc->msg(__('Писать запрещено'), 'write_denied');
        $can_write = false;
    }

    $accept_send = $ank->id && $ank->group && $ank->id != $user->id && $can_write;

    if ($ank->mail_only_friends && !$ank->is_friend($user)) {
        $accept_send = false;
        $doc->err(__('Писать сообщения могут только друзья'));
        if ($user->group > $ank->group) {
            $accept_send = true;
            $doc->msg(__('Ваш статус позволяет оставить сообщение данному пользователю несмотря на установленное ограничение'));
        }
    } elseif ($ank->id && $user->mail_only_friends && !$user->is_friend($ank) && $user->group >= $ank->group) {
        $doc->err(__('Пользователь не сможет Вам ответить'));
    }

    if ($accept_send && isset($_POST['post']) && isset($_POST['mess']) && isset($_POST['token']) && $user->group) {
        $mess = (string) $_POST['mess'];
        text::nickSearch($mess); // поиск и преобразование @nick
        $mess = text::input_text($mess);

        if (!antiflood::useToken($_POST['token'], 'mail')) {
            
        } elseif (!$mess) {
            $doc->err(__('Сообщение пусто'));
        } else {
            $ank->mess($mess, $user->id);

            if ($doc instanceof document_json) {
                $doc->form_value('mess', '');
                $doc->form_value('token', antiflood::getToken('mail'));
            }

            $user->balls += $dcms->add_balls_mail;
            $doc->msg(__('Сообщение успешно отправлено'));
            header('Refresh: 1; url=?id=' . $id_kont);
            exit();
        }
        if ($doc instanceof document_json)
            $doc->form_value('token', antiflood::getToken('mail'));
        $doc->ret(__('К сообщениям'), '?id=' . $id_kont);
    }

    $doc->title = __('Диалог с "%s"', $ank->login);

    if ($accept_send && !AJAX) {
        $form = new form(new url());
        $form->textarea('mess', __('Сообщение'), '', true);
        $form->hidden('token', antiflood::getToken('mail'));
        $form->button(__('Отправить'), 'post', false);
        $form->refresh_url("/my.mail.php?id=$id_kont&amp;" . passgen());
        $form->setAjaxUrl('/my.mail.php?id=' . $id_kont);
        $form->display();
    }


    $pages = new pages ();
    $res = $db->prepare("SELECT COUNT(*) FROM `mail` WHERE (`id_user` = ? AND `id_sender` = ?) OR (`id_user` = ? AND `id_sender` = ?)");
    $res->execute(Array($user->id, $id_kont, $id_kont, $user->id));
    $pages->posts = $res->fetchColumn(); // количество писем


    $q = $db->prepare("SELECT * FROM `mail` WHERE (`id_user` = ? AND `id_sender` = ?) OR (`id_user` = ? AND `id_sender` = ?) ORDER BY `id` DESC LIMIT " . $pages->limit);
    $q->execute(Array($user->id, $id_kont, $id_kont, $user->id));

    // отметка о прочтении писем
    $res = $db->prepare("UPDATE `mail` SET `is_read` = '1' WHERE `id_user` = ? AND `id_sender` = ?");
    $res->execute(Array($user->id, $id_kont));

    // уменьшаем кол-во непрочитанных писем на количество помеченных как прочитанные
    $user->mail_new_count = $user->mail_new_count - $res->rowCount();

    $id_after = false;

    $listing = new ui_components();
    $listing->ui_comment = true; //подключаем css comments
    $listing->ui_segment = true; //подключаем css segment
    $listing->class = $dcms->browser_type == 'full' ? 'segments large comments' : 'segments small comments';


    if ($arr = $q->fetchAll()) {
        foreach ($arr AS $mail) {
            $ank2 = new user((int) $mail ['id_sender']);

            $post = $listing->post();
            $post->class = 'segment comment';
            $post->comments = true;
            $post->id = 'mail_post_' . $mail['id'];
            $post->login = $ank2->nick();
            $post->url = '/profile.view.php?id=' . $ank2->id;
            $post->avatar = $ank2->getAvatar();
            $post->image_a_class = 'avatar';
            $post->content = text::toOutput($mail ['mess']);
            $post->highlight = !$mail ['is_read'];
            $post->time = misc::times($mail ['time']);

            if ($doc instanceof document_json) {
                $doc->add_post($post, $id_after);
            }

            $id_after = $post->id;
        }
    }
    if (isset($form)) {
        $listing->setForm($form);
    }
    $listing->setAjaxUrl('?id=' . $ank->id . '&amp;page=' . $pages->this_page);

    if ($doc instanceof document_json && !$arr && $user->group) {
        $post = new ui_compost(__('Нет результатов'));
        $post->icon('clone');
        $doc->add_post($post);
    }

    $listing->display(__('Нет результатов'));

    if ($doc instanceof document_json) {
        $doc->set_pages($pages);
    }

    $pages->display('?id=' . $ank->id . '&amp;'); // вывод страниц

    $doc->ret(__('Почта'), '/my.mail.php');
    $doc->ret(__('Личное меню'), '/menu.user.php');
    exit();
}

$res = $db->prepare("SELECT COUNT(*) FROM `mail` WHERE `id_user` = ? AND `is_read` = '0'");
$res->execute(Array($user->id));
$user->mail_new_count = $res->fetchColumn();

$pages = new pages ();

switch (@$_GET['from']) {
    case 'new':
        $from = 'new';

        $res = $db->prepare("SELECT COUNT(DISTINCT(`mail`.`id_sender`)) FROM `mail` WHERE `mail`.`id_user` = ? AND `mail`.`is_read` = '0'");
        $res->execute(Array($user->id));
        $pages->posts = $res->fetchColumn();
        $q = $db->prepare("SELECT `users`.`id`, `mail`.`id_sender`, `mail`.`mess`, MAX(`mail`.`time`) AS `time`, MIN(`mail`.`is_read`) AS `is_read`, COUNT(`mail`.`id`) AS `count` FROM `mail` LEFT JOIN `users` ON `mail`.`id_sender` = `users`.`id` WHERE `mail`.`id_user` = ? AND `mail`.`is_read` = '0' GROUP BY `mail`.`id_sender` ORDER BY `time` DESC LIMIT " . $pages->limit);

        break;
    default:
        $from = 'all';

        $res = $db->prepare("SELECT COUNT(DISTINCT(`mail`.`id_sender`)) FROM `mail` WHERE `mail`.`id_user` = ?");
        $res->execute(Array($user->id));
        $pages->posts = $res->fetchColumn();
        $q = $db->prepare("SELECT `users`.`id`, `mail`.`id_sender`, `mail`.`mess`, MAX(`mail`.`time`) AS `time`, MIN(`mail`.`is_read`) AS `is_read`, COUNT(`mail`.`id`) AS `count` FROM `mail` LEFT JOIN `users` ON `mail`.`id_sender` = `users`.`id` WHERE `mail`.`id_user` = ? GROUP BY `mail`.`id_sender` ORDER BY `time` DESC LIMIT " . $pages->limit);

        break;
}

$doc->tab(__('Новые'), '?from=new', $from === 'new');
$doc->tab(__('Все'), '?', $from === 'all');

$q->execute(Array($user->id));

$listing = new ui_components();
$listing->ui_comment = true; //подключаем css comments
$listing->ui_segment = true; //подключаем css segment
$listing->class = $dcms->browser_type == 'full' ? 'segments large comments' : 'segments small comments';

if ($arr = $q->fetchAll()) {
    foreach ($arr AS $mail) {
        $ank = new user((int) $mail['id_sender']);

        $post = $listing->post();
        $post->ui_label = true; //подключаем css label
        $post->class = 'segment comment';
        $post->comments = true;
        $post->avatar = $ank->getAvatar();
        $post->image_a_class = 'avatar';
        $post->url = '?id=' . $ank->id;
        $post->login = $ank->nick();
        $post->time = misc::times($mail['time']);
        $post->content = text::substr($mail['mess'], 30);
        $post->counter = $from == 'new' ? '+' . $mail['count'] : $mail['count'];
    }
}
$listing->display(__('Нет результатов'));

$pages->display('?');
$doc->ret(__('Личное меню'), '/menu.user.php');
