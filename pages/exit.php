<?php

$subdomain_theme_redirect_disable = true; // принудительное отключение редиректа на поддомены, соответствующие типу браузера
include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Выход');

if (isset($_POST['exit'])) {
    $res = $db->prepare("DELETE FROM `users_online` WHERE `id_user` = ?;");
    $res->execute(Array($user->id));

    $user->guest_init();

    setcookie(COOKIE_ID_USER);
    setcookie(COOKIE_USER_PASSWORD);
    unset($_SESSION);
    session_destroy();

    /* Инициализация механизма сессий  */
    session_name(SESSION_NAME) or die(__('Невозможно инициализировать сессии'));
    @session_start() or die(__('Невозможно инициализировать сессии'));

    $doc->msg(__('Авторизация успешно сброшена'));
    header('Refresh: 1; url=/index.php');
    exit;
}

$form = new form('?');
$form->bbcode(__("Вы действительно хотите сбросить авторизацию?"));
$form->button(__("Выйти"), 'exit');
$form->display();
