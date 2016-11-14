<?php

include_once '../sys/inc/start.php';
if (AJAX) {
    $doc = new document_json(1);
} else {
    $doc = new document(1);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ./');
    exit;
}
$ank = new user((int) $_GET['id']);

if (!$ank->group) {
    $doc->access_denied(__('Нет данных'));
}

if ($ank->id == $user->id) {
    header('Location: ./');
    exit;
}

$doc->title = __('Передать баллы "%s"', $ank->nick);

$doc->ret($ank->login, '/profile.view.php?id=' . $ank->id);

if (isset($_POST['balls'])) {
    $balls = abs((int) $_POST['balls']);

    if ($user->balls < $balls) {
        $doc->err(__('У Вас не достаточно баллов'));
    } elseif ($balls) {
        $user->balls -= $balls;
        $ank->balls += $balls;

        $ank->not(__('Перевод: %s баллов', $balls), $user->id);
        //$user->mess(__('Вы успешно передали %s баллов [user]%s[/user]', $balls, $ank->id));
        $doc->msg(__('Баллы успешно переданы'));
        header('Refresh: 1; ?id=' . $ank->id);
        exit;
    } else {
        $doc->err(__('Ошибка'));
        header('Refresh: 1; ?id=' . $ank->id);
        exit;
    }
}

$form = new form('?id=' . $ank->id);
$form->text('balls', __('Количество баллов'));
$form->button(__('Передать'));
$form->display();
