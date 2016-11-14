<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Смена пароля');

if (isset($_POST['save'])) {
    if (isset($_POST['password_old']) && crypt::hash($_POST['password_old'], $dcms->salt) == $user->password) {
        if (isset($_POST['password_new1']) && isset($_POST['password_new2'])) {
            if ($_POST['password_new1'] !== $_POST['password_new2'])
                $doc->err(__('Пароли не совпадают'));
            elseif (!is_valid::password($_POST['password_new1']))
                $doc->err(__('Не корректный новый пароль')); else {
                $_SESSION[SESSION_PASSWORD_USER] = $_POST['password_new1'];
                setcookie(COOKIE_USER_PASSWORD, crypt::encrypt($_POST['password_new1'], $dcms->salt_user), time() + 60 * 60 * 24 * 365);
                $user->password = crypt::hash($_POST['password_new1'], $dcms->salt);
                $doc->msg(__('Пароль успешно изменен'));
            }
        }
    } else
        $doc->err(__('Старый пароль неверен'));
}

$form = new form('?' . passgen());
$form->password('password_old', __('Старый пароль'));
$form->password('password_new1', __('Новый пароль'));
$form->password('password_new2', __('Подтверждение'));
$form->button(__('Применить'), 'save');
$form->display();

$doc->ret(__('Личное меню'), '/menu.user.php');