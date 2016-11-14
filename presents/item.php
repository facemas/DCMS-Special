<?php

include_once '../sys/inc/start.php';

$doc = new document(1);
$doc->title = __('Отправить подарок');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора подарка'));
    exit;
}

if (!isset($_GET['user']) || !is_numeric($_GET['user'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора пользователя'));
    exit;
}

$id_present = (int) $_GET['id'];
$ank = new user((int) $_GET['user']);

if (!$ank->group) {
    header('Refresh: 1; url=/profile.view.php?id=' . $user->id . '&' . passgen());
    $doc->access_denied(__('Нет данных'));
}

if ($ank->id == $user->id) {
    header('Refresh: 1; url=/profile.view.php?id=' . $user->id . '&' . passgen());
    $doc->err(__('Ошибка операция подарка'));
    exit;
}



$q = $db->prepare("SELECT * FROM `present_items` WHERE `id` = ?");
$q->execute(Array($id_present));
if (!$item = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Подарок не доступна'));
    exit;
}

$doc->title .= ' - ' . $ank->nick;

if (isset($_POST['text'])) {
    if ($user->balls >= $item['ball']) {
        $user->balls -= $item['ball'];
        $text = text::for_name($_POST['text']);

        $res = $db->prepare("INSERT INTO `present_users` (`id_user`, `id_ank`, `id_present`, `time`, `text`)VALUES (?, ?, ?, ?, ?)");
        $res->execute(Array($ank->id, $user->id, $id_present, TIME, $text));
        if ($user->group && $ank->id != $user->id) {
            $ank->not("Подарил" . ($user->sex ? '' : 'а') . " Вам  [url=/profile.presents.php?id=" . $ank->id . "]подарок[/url]", $user->id);
        }
        $doc->msg(__('Подарок успешно отправлено'));

        header('Refresh: 1; url=/profile.view.php?id=' . $ank->id . '&' . passgen());
        $doc->ret(__('В анкету'), "/profile.view.php?id={$ank->id}");
        exit;
    } else {
        $doc->err(__('Не хватает баллов'));
    }
}

$listing = new listing();
$post = $listing->post();

if (is_file(H . $screen = '/sys/images/presents/' . $id_present . '.png')) {
    $post->title = '<img  src="' . $screen . '" style="max-width: 80px;"/>';
}
$post->title .= $ank->nick();
$post->counter = __('%s', ($item['ball'] == 0 ? __('Бесплатно') : "<i class='fa fa-gg-circle fa-fw'></i> $item[ball]"));
$listing->display();


$form = new form('?id=' . $id_present . '&user=' . $ank->id . '&' . passgen() . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
$form->textarea('text', __('Комментарий'));
$form->button(__('Отправить'));
$form->display();

if (isset($_GET['return']))
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
else
    $doc->ret(__('В категорию'), 'category.php?id=' . $item['id_category'] . '&user=' . $ank->id);

$doc->ret(__('В анкету'), "/profile.view.php?id={$ank->id}");
