<?php

include_once '../sys/inc/start.php';
$doc = new document(2);
$doc->title = __('Действия');

$user_actions = new menu_ini('user_actions');

if (isset($_GET['id'])) {
    $ank = new user($_GET['id']);
} else {
    $ank = $user;
}

if (!$ank->group) {
    $doc->toReturn();
    $doc->err(__('Нет данных'));
    exit;
}

$doc->title .= ' "' . $ank->login . '"';

$user_actions->value_add('id', $ank->id);

if ($ank->group >= $user->group) {
    $doc->toReturn();
    $doc->err(__('Ваш статус не позволяет производить действия с данным пользователем'));
    exit;
}

$user_actions->display();

$doc->ret(__('Анкета "%s"', $ank->login), '/profile.view.php?id=' . $ank->id);
$doc->ret(__('Управление'), '/dpanel/');