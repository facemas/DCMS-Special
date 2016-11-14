<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(4);
$doc->title = __('Подозрительные пользователи');

if (!empty($_GET['approve'])) {
    $app = (int) $_GET['approve'];
    $res = $db->prepare("SELECT COUNT(*)AS cnt FROM `users_suspicion` WHERE `id_user` = ?");
    $res->execute(Array($app));
    $k = $res->fetchColumn();
    if ($k) {
        $res = $db->prepare("DELETE FROM `users_suspicion` WHERE `id_user` = ? LIMIT 1");
        $res->execute(Array($app));
        $ank = new user($app);
        $doc->msg(__('Пользователь %s успешно одобрен', $ank->login));
    }
}

if (isset($_GET['id'])) {
    $ank = new user((int) $_GET['id']);
    if (!$ank->id) {
        $doc->err(__('Пользователь не найден'));
        $doc->ret(__('Подозрительные пользователи'), '?');
        $doc->ret(__('Управление'), '/dpanel/');
        exit;
    }

    $q = $db->prepare("SELECT *  FROM `users_suspicion` WHERE `id_user` = ?");
    $q->execute(Array($ank->id));
    if (!$sus = $q->fetch()) {
        $doc->err(__('Выбранный пользователь отсутствует в списке подозрительных'));
        $doc->ret(__('Подозрительные пользователи'), '?');
        $doc->ret(__('Управление'), '/dpanel/');
        exit;
    }
    $listing = new listing();

    $post = $listing->post();
    $post->title = $ank->nick();
    $post->icon($ank->icon());
    $post2 = __('E-mail: %s', $ank->reg_mail) . "\n";
    $post2 .= __('Фраза: %s', $sus['text']);
    $post->content = text::toOutput($post2);

    $post = $listing->post();
    $post->icon('approve');
    $post->title = __('Подтвердить регистрацию');
    $post->url = "?approve=$ank->id";

    $post = $listing->post();
    $post->icon('shit');
    $post->title = __('Забанить пользователя');
    $post->url = "user.ban.php?id_ank=$ank->id";

    $post = $listing->post();
    $post->icon('delete');
    $post->title = __('Удалить пользователя');
    $post->url = "user.delete.php?id_ank=$ank->id";

    $listing->display();
    $doc->ret(__('Подозрительные пользователи'), '?');
    $doc->ret(__('Управление'), '/dpanel/');
    exit;
}

$listing = new listing();
$res = $db->query("SELECT COUNT(*) FROM `users_suspicion`");
$pages = new pages;
$pages->posts = $res->fetchColumn(); // количество постов

$q = $db->query("SELECT *  FROM `users_suspicion` ORDER BY `id_user` ASC LIMIT " . $pages->limit);
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $sus) {
        $ank = new user($sus['id_user']);

        $post = $listing->post();
        $post->url = '?id=' . $ank->id;
        $post->title = $ank->nick();
        $post->icon($ank->icon());
        $post2 = __('E-mail: %s', $ank->reg_mail) . "\n";
        $post2 .= __('Фраза: %s', $sus['text']);
        $post->content[] = $post2;
    }
}

$listing->display(__('Нет подозрительных пользователей'));

$pages->display('?'); // вывод страниц
$doc->ret(__('Управление'), '/dpanel/');
