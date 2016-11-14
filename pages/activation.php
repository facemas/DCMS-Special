<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Активация аккаунта');

if ($user->group) {
    $doc->access_denied(__('Вы уже зарегистрированы'));
}

if (empty($_GET['id']) || empty($_GET['code'])) {
    $doc->access_denied(__('Ошибка активации аккаунта'));
}

$tuser = new user($_GET['id']);

if ($tuser->group) {
    if (!$tuser->a_code) {
        $doc->msg(__('Аккаунт уже активирован'));
        echo __('Теперь Вы можете <a href="%s">Авторизоваться</a>', '/login.php' . (isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : null));
        exit;
    } elseif ($tuser->a_code != $_GET['code'])
        $doc->err(__('Неверный код активации'));
    else {
        $tuser->a_code = null;
        $doc->msg(__('Учетная запись успешно активирована'));
        echo __('Теперь Вы можете <a href="%s">Авторизоваться</a>', '/login.php' . (isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : null));
        exit;
    }
} else
    $doc->err(__('Учетная запись не найдена'));
echo __('Попробуйте <a href="%s">Зарегистрироваться</a> заново', '/reg.php' . (isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : null));