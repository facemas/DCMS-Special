<?php
include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Жалоба на пользователя');



$can_write = true;
if (!$user->is_writeable) {
    $doc->msg(__('Вы не можете оставить жалобу'), 'write_denied');
    if (!empty($_GET['return'])) {
        $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
    }
    exit;
}



$ank = new user(@$_GET['id']);

if (!$ank->group || $ank->group > $user->group) {
    $doc->toReturn();
    $doc->err(__('Пользователь не найден'));
    exit;
}

$menu = new menu_code('code'); // загружаем меню кодекса
$doc->title = __('Жалоба на "%s"', $ank->login);

if (isset($_POST['complaint'])) {
    $link = !empty($_POST['link']) ? (string) $_POST['link'] : false;
    $code = !empty($_POST['code']) ? (string) $_POST['code'] : false;
    $comm = text::input_text(@$_POST['comment']);

    $res = $db->prepare("SELECT * FROM `complaints` WHERE `id_user` = ? AND `id_ank` = ? AND `link` = ? AND `time` > ?");
    $res->execute(Array($user->id, $ank->id, $link, NEW_TIME));
    if (!$link) {
        $doc->err(__('Не указана ссылка на нарушение'));
    } elseif (!isset($menu->menu_arr[$code])) {
        $doc->err(__('Не выбрано нарушение'));
    } elseif (!$comm) {
        $doc->err(__('Необходимо прокомментировать жалобу'));
    } elseif ($res->fetch()) $doc->err(__('Вы уже жаловались сегодня на этого пользователя'));
    else {
        $doc->toReturn();

        $res = $db->prepare("INSERT INTO `complaints` (`time`, `id_user`, `id_ank`, `link`, `code`, `comment`) VALUES (?, ?, ?, ?, ?, ?)");
        $res->execute(Array(TIME, $user->id, $ank->id, $link, $code, $comm));
        $doc->msg(__('Жалоба будет рассмотрена модератором'));

        $mess = "Поступила [url=/dpanel/user.complaints.php]жалоба[/url] на пользователя [user]$ank->id[/user] от [user]$user->id[/user]";
        $admins = groups::getAdmins(2);
        foreach ($admins AS $admin) {
            $admin->mess($mess);
        }



        if (!empty($_GET['return'])) {
            $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
        }

        exit;
    }
}

$link = !empty($_GET['link']) ? $_GET['link'] : (!empty($_POST['link']) ? $_POST['link'] : false);

$form = new form(new url());
$form->text('link', __('Ссылка'), $link);
$form->select('code', __('Нарушение'), $menu->options());
$form->textarea('comment', __('Комментарий'));
$form->button(__('Пожаловаться'), 'complaint');
$form->display();

if (!empty($_GET['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
}
