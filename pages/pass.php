<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Восстановление пароля');

if ($user->group) {
    $doc->err(__('Вы уже авторизованы'));
    exit;
}

if (!empty($_GET['id']) && !empty($_GET['code'])) {
    $doc->ret(__('Восстановление пароля'), '?' . passgen());

    $id = (int) $_GET['id'];
    $code = preg_replace('#[^a-z0-9]#i', '', $_GET['code']);

    $ank = new user($id);

    if (!$ank->group) {
        $doc->err(__('Пользователь с ID#%s не зарегистрирован', $id));
        exit;
    }

    if (!$ank->recovery_password || $ank->recovery_password !== $code) {
        $doc->err(__('Ключ для восстановления пароля не действителен'));
        exit;
    }

    $doc->title = __('Восстановление пароля к "%s"', $ank->login);

    if (isset($_POST['password1']) && isset($_POST['password2'])) {
        if ($_POST['password1'] !== $_POST['password2'])
            $doc->err(__('Пароли не совпадают'));
        elseif (!is_valid::password($_POST['password1']))
            $doc->err(__('Не корректный новый пароль'));
        else {
            $ank->password = crypt::hash($_POST['password1'], $dcms->salt);
            $ank->recovery_password = '';
            $doc->msg(__('Пароль успешно изменен'));
            header('Refresh: 2; url=/login.php?' . passgen());
            exit;
        }
    }

    $form = new form(new url());
    $form->password('password1', __('Новый пароль'));
    $form->password('password2', __('Подтвердите пароль'));
    $form->button(__('Применить'), 'save');
    $form->display();
    exit;
}

if (isset($_POST['post'])) {
    if (!is_valid::mail(@$_POST['mail']))
        $doc->err(__('Указан не корректный E-mail'));
    else {
        $mail = $_POST['mail'];
        $q = $db->prepare("SELECT `id` FROM `users` WHERE `reg_mail` = ? ORDER BY `id` DESC LIMIT 1");
        $q->execute(Array($mail));

        if (!$row = $q->fetch()) {
            $doc->err(__('Учетная запись, зарегистрированная на данный Email не обнаружена'));
        } else {
            $ank = new user($row['id']);
            $ank->recovery_password = $recovery_password = md5(passgen(100));

            $t = new design();
            $t->assign('title', __('Восстановление пароля'));
            $t->assign('login', $ank->login);
            $t->assign('site', $dcms->sitename);
            $t->assign('url', 'http://' . $_SERVER['HTTP_HOST'] . '/pass.php?id=' . $ank->id . '&amp;code=' . $recovery_password);

            if (mail::send($mail, __('Восстановление пароля'), $t->fetch('file:' . H . '/sys/templates/mail.pass.tpl'))) {
                $step = 3;
                $doc->msg(__('На Ваш E-mail отправлено письмо с ссылкой для активации аккаунта'));
            } else {
                $doc->err(__('Ошибка при отправке email, попробуйте позже'));
            }
        }
    }
}

$form = new form('?' . passgen());
$form->input('mail', __('Ваш E-mail'));
$form->button(__('Продолжить'), 'post');
$form->display();

