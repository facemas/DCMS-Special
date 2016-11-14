<?php
include_once '../sys/inc/start.php';
dpanel::check_access();
$groups = groups::load_ini();
$doc = new document(4);
$doc->title = __('Удаление пользователя');

if (isset($_GET['id_ank'])) $ank = new user($_GET['id_ank']);
else $ank = $user;

if (!$ank->group) {
    $doc->toReturn();
    $doc->err(__('Нет данных'));
    exit;
}

$doc->title .= ' "' . $ank->nick . '"';

if ($ank->group >= $user->group) {
    $doc->toReturn();
    $doc->err(__('Ваш статус не позволяет производить действия с данным пользователем'));
    exit;
}

$tables = ini::read(H . '/sys/ini/user.tables.ini', true);

if (isset($_POST['delete'])) {
    if (empty($_POST['captcha']) || empty($_POST['captcha_session']) || !captcha::check($_POST['captcha'],
            $_POST['captcha_session'])) {
        $doc->err(__('Проверочное число введено неверно'));
    } else {
        misc::user_delete($ank->id);
        $dcms->log('Пользователи', 'Удаление пользователя ' . $ank->nick . ' (ID ' . $ank->id . ')');
        $doc->msg(__('Пользователь успешно удален'));
        $doc->ret(__('Админка'), '/dpanel/');
        exit;
    }
}

$listing = new listing();
foreach ($tables AS $name => $v) {
    $post = $listing->post();
    $post->title = $name;
    $res = $db->prepare("SELECT COUNT(*) FROM " . $v['table'] . " WHERE " . $v['row'] . " = ?");
    $res->execute(Array($ank->id));
    $post->counter = $res->fetchColumn();
    $post->highlight = (bool) $post->counter;
}
$listing->display();

$form = new form(new url());
$form->captcha();
$form->bbcode(__('Пользователь будет удален без возможности восстановления. Подтвердите удаление пользователя "%s".',
        $ank->nick));
$form->button(__('Удалить'), 'delete');
$form->display();

$doc->ret(__('Действия'), 'user.actions.php?id=' . $ank->id);
$doc->ret(__('Анкета "%s"', $ank->login), '/profile.view.php?id=' . $ank->id);
$doc->ret(__('Админка'), '/dpanel/');
