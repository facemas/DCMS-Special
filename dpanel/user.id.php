<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$groups = groups::load_ini();
$doc = new document(6);
$doc->title = __('Изменение ID пользователя');

if (isset($_GET['id_ank']))
    $ank = new user($_GET['id_ank']);
else
    $ank = $user;

if (!$ank->group) {
    $doc->toReturn();
    $doc->err(__('Нет данных'));
    exit;
}

$doc->title .= ' "' . $ank->login . '"';

if ($ank->group >= $user->group) {
    $doc->toReturn();
    $doc->err(__('Ваш статус не позволяет производить действия с данным пользователем'));
    exit;
}

$tables = ini::read(H . '/sys/ini/user.tables.ini', true);

if (isset($_POST['change'])) {
    $id_new = (int) @$_POST['id_new'];
    $id_old = $ank->id;
    $res = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `id` = ?");
    $res->execute(Array($id_new));
    if (empty($_POST['captcha']) || empty($_POST['captcha_session']) || !captcha::check($_POST['captcha'], $_POST['captcha_session'])) {
        $doc->err(__('Проверочное число введено неверно'));
    } elseif ($id_new < 0) {
        $doc->err(__('Идентификатор не может быть отрицательным'));
    } elseif ($id_new == $id_old) {
        $doc->err(__('Нет изменений'));
    } elseif ($id_new == 0) {
        $doc->err(__('Идентификатор 0 зарезервирован'));
    } elseif ($res->fetchColumn()) {
        $doc->err(__('Идентификатор занят другим пользователем'));
    } else {

        foreach ($tables AS $d) {
            $res = $db->prepare("UPDATE `" . $d['table'] . "` SET `" . $d['row'] . "` = ? WHERE `" . $d['row'] . "` = ?");
            $res->execute(Array($id_new, $id_old));
        }
        $res = $db->prepare("UPDATE `users` SET `id` = ? WHERE `id` = ?");
        $res->execute(Array($id_new, $id_old));
        $dcms->log('Пользователи', 'Изменение ID пользователя ' . $ank->login . ' с ' . $id_old . ' на ' . $id_new . ')');

        $doc->msg(__('Идентификатор пользователя успешно изменен'));
        $doc->ret(__('Админка'), '/dpanel/');
        exit;
    }
}

$form = new form();
$form->text('id_new', __('Новый ID'), $ank->id);
$form->captcha();
$form->bbcode('[notice] ' . __('Изменение ID пользователя может повлечь ошибки в сторонних модулях.'));
$form->button(__('Применить'), 'change');
$form->display();

$doc->ret(__('Действия'), 'user.actions.php?id=' . $ank->id);
$doc->ret(__('Анкета "%s"', $ank->login), '/profile.view.php?id=' . $ank->id);
$doc->ret(__('Админка'), '/dpanel/');
