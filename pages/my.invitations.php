<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Пригласительные');

if (isset($_GET['id'])) {
    $id_inv = (int)$_GET['id'];
    $q = $db->prepare("SELECT * FROM `invations` WHERE `id` = ? AND `id_user` = ? AND `id_invite` IS NULL LIMIT 1");
    $q->execute(Array($id_inv, $user->id));


    if (!$inv = $q->fetch()) {
        header('Refresh: 1; url=?');
        $design->err(__('Пригласительный не найден'));
        $design->ret(__('К пригласительным'), '?');
        $design->head($title); // шапка страницы
        $design->title($title); // заголовок страницы
        $design->foot(); // ноги
        exit;
    }

    if (isset($_POST['delete']) && $inv['time_reg'] < TIME - 86400) {
        $res = $db->prepare("DELETE FROM `invations` WHERE `id` = ? LIMIT 1");
        $res->execute(Array($inv['id']));
        header('Refresh: 1; url=?');
        $doc->msg(__('Пригласительный успешно удален'));
        $doc->ret(__('К пригласительным'), '?');
        exit;
    }

    if (isset($_POST['email']) && !$inv['email']) {
        if (!is_valid::mail($_POST['email']))
            $doc->err(__('Указан не корректный E-mail'));
        else {
            $email = $_POST['email'];
            $inv['code'] = passgen();
            $t = new design();
            $t->assign('title', __('Пригласительный'));
            $t->assign('login', $user->login);
            $t->assign('site', $dcms->sitename);
            $t->assign('url', 'http://' . $_SERVER['HTTP_HOST'] . '/reg.php?invite=' . $inv['code']);

            if (mail::send($email, __('Приглашение'), $t->fetch('file:' . H . '/sys/templates/mail.invite.tpl'))) {
                $res = $db->prepare("UPDATE `invations` SET `email` = ?, `time_reg` = ?, `code` = ? WHERE `id` = ? LIMIT 1");
                $res->execute(Array($email, TIME, $inv['code'], $inv['id']));
                header('Refresh: 1; url=?');
                $doc->msg(__('Пригласительный успешно отправлен'));
                $doc->ret(__('К пригласительным'), '?');

                exit;
            } else
                $doc->err(__('Ошибка при отправке email, попробуйте позже'));
        }
    }

    $doc->title = __("Пригласительный #%s", $inv['id']);
    $doc->ret(__('К пригласительным'), '?');

    if ($inv['email']) {
        echo __('Пригласительный отправлен на email: %s', $inv['email']) . "<br />";
        echo __("Отправлен: %s", misc::when($inv['time_reg'])) . "<br />";

        if ($inv['time_reg'] < TIME - 86400) {
            if (isset($_GET['delete'])) {
                $form = new form(new url());
                $form->bbcode(__('Подтвердите удаление пригласительного'));
                $form->bbcode(__('Его место займет новый пригласительный'));
                $form->button(__('Удалить'), 'delete');
                $form->display();
            }

            $doc->act(__('Удалить приглашение'), "?id=$inv[id]&amp;delete");
        } else {
            echo __("В случае ошибки или отказа от пригласительного его можно будет удалить по истечению суток с момента отправки");
        }
    } else {
        $form = new form(new url());
        $form->input('email', __('Email'));
        $form->button(__('Отправить'));
        $form->display();
    }
    exit;
}

$k_inv = (int)($user->balls / $dcms->balls_for_invite); // количество пригласительных
$doc->msg(__("У Вас %s пригласительны" . misc::number($k_inv, 'й', 'x', 'х'), $k_inv), 'invations');
$res_cnt_inv = $db->prepare("SELECT COUNT(*) FROM `invations` WHERE `id_user` = ?");
$res_cnt_inv->execute(Array($user->id));
$k = $res_cnt_inv->fetchColumn();

if ($k_inv > $k) {
    // пополняем список пригластельных
    $k_add = $k_inv - $k;
    $arr_ins = array();
    $res = $db->prepare("INSERT INTO `invations` (`id_user`) VALUES (?);");
    for ($i = 0; $i < $k_add; $i++) {
        $res->execute(Array($user->id));
    }
}


$res_cnt_inv->execute(Array($user->id));
$pages = new pages();
$pages->posts = $res_cnt_inv->fetchColumn(); // количество пригласительных

$q = $db->prepare("SELECT * FROM `invations` WHERE `id_user` = ? ORDER BY (`id_invite` IS NULL) DESC, (`email` IS NULL) ASC, `id` ASC LIMIT " . $pages->limit);
$q->execute(Array($user->id));

$listing = new listing();
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $inv) {
        $post = $listing->post();
        $post->icon('invite');
        if ($inv['id_invite']) {
            $ank = new user($inv['id_invite']);
            $post->time = misc::when($inv['time_reg']);
            $post->content = __('Использован');
            $post->title = $ank->nick();
            $post->url = '/profile.view.php?id=' . $ank->id;
        } elseif ($inv['email']) {
            $post->url = '?id=' . $inv['id'];
            $post->title = __('Пригласительный #%s', $inv['id']);
            $post->content = __('Отправлен на email: %s', $inv['email']) . '<br />';
            if (!$inv['code']) {
                $post->content .= __('Активирован');
            }
            if ($inv['time_reg'] < TIME - 86400) {
                // 86400 секунд = 1 сутки - вчемя, через которое можно деактивировать неиспользованный пригласительный
                $post->action('delete', "?id={$inv['id']}&amp;delete");
            }
        } else {
            $post->title = "<a href='?id=$inv[id]'>" . __('Пригласительный #%s', $inv['id']) . "</a>";
            $post->content = __('Не использован');
        }
    }
}
$listing->display(__('Список пригласительных пуст'));

$pages->display('?'); // вывод страниц

$doc->ret(__('Личное меню'), '/menu.user.php');