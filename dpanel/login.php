<?php

include_once '../sys/inc/start.php';
$doc = new document(2);
$doc->title = __('Вход в админку');

if (!dpanel::is_access() && (empty($_POST['captcha_session']) || empty($_POST['captcha']))) {
    $doc->msg(__('Для входа в админку необходимо пройти капчу'));
} elseif (!dpanel::is_access() && !captcha::check($_POST['captcha'], $_POST['captcha_session'])) {
    $doc->err(__('Вы ошиблись при вводе чисел с картинки'));
} else {
    dpanel::access(); // разрешаем доступ к админке

    $doc->msg(__('Отлично, переходим в админку'));
    $doc->toReturn('/dpanel/', 2);
    exit;
}

$form = new form(new url());
$form->captcha();
if (preg_match('#Opera mobile#ui', $dcms->browser))
    $form->bbcode('[notice] '.__('Функция Turbo должна быть отключена'));
$form->button(__('Войти'));
$form->display();