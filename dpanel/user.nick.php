<?php
include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(4);
$doc->title = __('Изменение ника');

if (isset($_GET['id_ank'])) $ank = new user($_GET['id_ank']);
else $ank = $user;

if (!$ank->group) {
    $doc->toReturn();
    $doc->err(__('Нет данных'));
    exit;
}

if (!$ank->vk_id) {
    $doc->toReturn();
    $doc->err(__('Нельзя переименовать пользователя vk.com'));
    exit;
}

$doc->title .= ' "' . $ank->login . '"';

if ($ank->group >= $user->group) {
    $doc->toReturn();
    $doc->err(__('Ваш статус не позволяет производить действия с данным пользователем'));
    exit;
}

if (isset($_POST['save']) && !empty($_POST['login']) && $_POST['login'] != $ank->login) {
    $login = (string) $_POST['login'];

    $res = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `login` = ?");
    $res->execute(Array($login));
    if (!is_valid::nick($login)) {
        $doc->err(__('Не корректный Ник'));
    } elseif ($login != htmlspecialchars($login)) {
        $doc->msg(__('В нике содержатся запрещенные символы'));
    } elseif ($res->fetchColumn()) {
        $doc->err(__('Пользователь с таким ником уже зарегистрирован'));
    } else {
        $dcms->log('Пользователи',
            'Изменение ника пользователя ' . $ank->login . ' на [url=/profile.view.php?id=' . $ank->id . ']' . $login . '[/url]');

        $ank->login = $login;
        $doc->msg(__('Ник успешно изменен'));
    }
}

$form = new form(new url());
$form->text('login', __('Логин/ник'), $ank->login);
$form->button(__('Применить'), 'save');
$form->display();

$doc->ret(__('Действия'), 'user.actions.php?id=' . $ank->id);
$doc->ret(__('Анкета "%s"', $ank->login), '/profile.view.php?id=' . $ank->id);
$doc->ret(__('Админка'), '/dpanel/');
