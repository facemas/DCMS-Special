<?php

include_once '../sys/inc/start.php';
$doc = new document(); // инициализация документа для браузера
$doc->title = __('Отзывы');

if (isset($_GET['id'])) {
    $ank = new user($_GET['id']);
} else {
    $ank = $user;
}

if (!$ank->group) {
    $doc->access_denied(__('Нет данных'));
}

$can_write = true;
if (!$user->is_writeable) {
    $doc->msg(__('Писать запрещено'), 'write_denied');
    $can_write = false;
}

$add = 1;

$q = $db->prepare("SELECT COUNT(*) AS `count`, MAX(`time`) AS `time` FROM `reviews_users` WHERE `id_user` = ? AND `id_ank` = ?");
$q->execute(Array($user->id, $ank->id));
if ($row = $q->fetch()) {
    $count = $row['count'];
    $time = $row['time'];
} else {
    $count = 0;
    $time = 0;
}


// чем больше отзывов оставлено, тем меньше это влияет на рейтинг
$add = 1 - min($count, 9) / 10;
// оставлять отзыв можно не чаще одного раза в сутки
if ($time > NEW_TIME) {
    $add = 0;
}
// VIP пользователю рейтинг засчитывается вдвойне
if ($ank->is_vip) {
    $add += $add;
}

if ($ank->id == $user->id) {
    $doc->title = __('Отзывы обо мне');
} else {
    $doc->title = __('"Отзывы о "%s"', $ank->login);
}

if ($user->group && $can_write && isset($_POST['review']) && $user->id != $ank->id && $add) {
    $message = text::input_text($_POST['review']);

    if ($message) {
        $res = $db->prepare("INSERT INTO `reviews_users` (`id_user`, `id_ank`, `time`, `text`, `rating`) VALUES (?, ?, ?, ?, ?)");
        $res->execute(Array($user->id, $ank->id, TIME, $message, $add));
        $res = $db->prepare("UPDATE `users` AS `u` SET `u`.`rating` = (SELECT SUM(`rating`) FROM `reviews_users` AS `ru` WHERE `ru`.`id_ank` = :id_user) WHERE `u`.`id` = :id_user LIMIT 1");
        $res->execute(Array(':id_user' => $ank->id));

        header('Refresh: 1; url=?id=' . $ank->id);
        $doc->ret(__('Вернуться'), '?id=' . $ank->id);
        $doc->msg(__('Ваш отзыв успешно оставлен'));

        $ank->not(__(($user->sex ? 'Оставил' : 'Оставила') . " о Вас свой [url=/profile.reviews.php]отзыв[/url]"), $user->id);

        exit;
    } else {
        $doc->err(__('Текст отзыва пуст'));
    }
}

switch (@$_GET['from']) {
    case 'users':
        $from = 'users';
        break;
    case 'all':
        $from = 'all';
        break;
    default:
        $from = 'users';
        break;
}

$doc->tab(__('От пользователей'), '?id=' . $ank->id . '&amp;from=users', $from === 'users');
$doc->tab(__('Все'), '?id=' . $ank->id . '&amp;from=all', $from === 'all');

$pages = new pages;
$res = $db->prepare("SELECT COUNT(*) FROM `reviews_users` WHERE `id_ank` = ?" . ($from === 'users' ? ' AND `id_user` <> 0' : ''));
$res->execute(Array($ank->id));
$pages->posts = $res->fetchColumn();

$q = $db->prepare("SELECT * FROM `reviews_users` WHERE `id_ank` = ? " . ($from === 'users' ? ' AND `id_user` <> 0' : '') . " ORDER BY `id` DESC LIMIT $pages->limit");
$q->execute(Array($ank->id));

$listing = new listing();
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $rev) {
        $post = $listing->post();
        if ($rev['id_user']) {
            $ank2 = new user($rev['id_user']);
        } else {
            $ank2 = new user(0);
        }

        $post->counter = $rev['rating'] > 0 ? '+' . $rev['rating'] : $rev['rating'];
        $post->icon($ank2->icon());
        $post->title = $ank2->nick();
        if ($rev['forum_message_id']) {
            $post->content[] = __('За [url=%s]сообщение[/url] в форуме', '/forum/message.php?id_message=' . $rev['forum_message_id']);
        } else {
            $post->content = text::toOutput($rev['text']);
        }

        $post->url = '/profile.view.php?id=' . $ank2->id;
    }
}
$listing->display(__('Отзывы отсутствуют'));

$pages->display('?id=' . $ank->id . '&amp;from=' . $from . '&amp;'); // вывод страниц

if ($user->group && $can_write && $user->id != $ank->id && $add) {
    $form = new form(new url());
    $form->textarea('review', __('Отзыв о пользователе') . ' *');
    $form->bbcode('* ' . __('Разрешается оставлять только положительные отзывы. Кроме того каждый отзыв увеличивает пользователю рейтинг.'));
    $form->button(__('Отправить'));
    $form->display();
}

$doc->ret(__('В анкету'), "profile.view.php?id={$ank->id}");
$doc->ret(__('Личное меню'), '/menu.user.php');
