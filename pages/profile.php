<?php

$ank = (empty($_GET ['id'])) ? $user : new user((int) $_GET ['id']);

$from = 'default';
$doc->tab(__('Активность'), '?act=activity&amp;id=' . $ank->id, $from === 'activity');
$doc->tab(__('Анкета'), '?act=anketa&amp;id=' . $ank->id, $from === 'anketa');
$doc->tab(__('Основное'), '?id=' . $ank->id, $from === 'default');

# Выводим подарки если есть
$res = $db->prepare("SELECT COUNT(*) FROM `present_users` WHERE `id_user` = ?");
$res->execute(Array($ank->id));
$gift = $res->fetchColumn();

if ($gift > 0) {

    $post = $listing->post();
    $post->highlight = true;
    $post->icon('gift');
    $post->url = "/profile.presents.php?id=$ank->id";
    $post->title = __('Подарки %s', $ank->nick);
    if ($gift) {
        $post->counter = '<i class="fa fa-gift fa-fw"></i> ' . $gift;
    }

    $presents = '';
    $q = $db->prepare("SELECT * FROM `present_users` WHERE `id_user` = ? ORDER BY `id` DESC LIMIT 5"); //запилить настройку сколько выводить
    $q->execute(Array($ank->id));
    while ($item = $q->fetch()) {
        if (is_file(H . $screen = '/sys/images/presents/' . $item['id_present'] . '.png')) {
            $presents .= '<img class="podarki_photo" src="' . $screen . '" style="float:left; max-width: 80px;"/>';
        }
    }
    if ($presents) {
        $post = $listing->post();
        $post->title = $presents;
    }
}
$listing->display();

$listing = new ui_components();
$listing->ui_segment = true; //подключаем css segment
$listing->ui_list = true; //подключаем css list
$listing->class = 'segments';

$post = $listing->post();
$post->class = 'ui secondary segment';
$post->icon('th-large');
$post->title = __('Меню %s', $ank->nick);

# фотографии
if ($ank->id) {

// папка фотоальбомов пользователей
    $photos = new files(FILES . '/.photos');
// папка альбомов пользователя
    $albums_path = FILES . '/.photos/' . $ank->id;

    if (!is_dir($albums_path)) {
        if ($albums_dir = $photos->mkdir($ank->login, $ank->id)) {
            $albums_dir->group_show = 0;
            $albums_dir->group_write = min($ank->group, 2);
            $albums_dir->group_edit = max($ank->group, 4);
            $albums_dir->id_user = $ank->id;
            unset($albums_dir);
        }
    }

    $albums_dir = new files($albums_path);

    $photos_count['all'] = $albums_dir->count();
    $photos_count['new'] = $albums_dir->count(NEW_TIME);

    $post = $listing->post();
    $post->list = true;
    $post->class = 'ui segment';
    $post->title = __('Фотографии');
    $post->icon('image');
    $post->url = '/photos/albums.php?id=' . $ank->id;
    if ($photos_count['new']) {
        $post->counter = '+' . $photos_count ['new'];
    }
}

# Подарки
if ($ank->id) {
    $res = $db->prepare("SELECT COUNT(*) FROM `present_users` WHERE `id_user` = ?");
    $res->execute(Array($user->id));
    $gifts = $res->fetchColumn();
    $post = $listing->post();
    $post->list = true;
    $post->class = 'ui segment';
    $post->icon('gift');
    $post->title = __('Подарки');
    $post->url = '/profile.presents.php?id=' . $ank->id;
    if ($gifts != 0) {
        $post->counter = '+' . $gifts;
    }
}

# Отзывы
$post = $listing->post();
$post->list = true;
$post->class = 'ui segment';
$post->title = __('Отзывы');
$post->icon('ticket');
$post->url = '/profile.reviews.php?id=' . $ank->id;
$post->counter = $ank->rating;

if ($user->id && $user->id == $ank->id) {
    # Гости
    $res = $db->prepare("SELECT COUNT(*) FROM `my_guests` WHERE `id_ank` = ? AND `read`= ?");
    $res->execute(Array($user->id, 1));
    $new_g = $res->fetchColumn();
    $post = $listing->post();
    $post->list = true;
    $post->class = 'ui segment';
    $post->icon('user-o');
    $post->title = __('Гости');
    $post->url = '/my.guest.php';
    if ($new_g != 0) {
        $post->counter = '+' . $new_g;
    }

    # Почта
    $post = $listing->post();
    $post->list = true;
    $post->class = 'ui segment';
    $post->icon('envelope-o');
    $post->title = __('Почта');
    $post->url = '/my.mail.php';

    $res = $db->prepare("SELECT COUNT(*) FROM `notification` WHERE `id_user` = ? AND `is_read`= ?");
    $res->execute(Array($user->id, 1));
    $not = $res->fetchColumn();
    $post = $listing->post();
    $post->list = true;
    $post->class = 'ui segment';
    $post->icon('bell-o');
    $post->title = __('Уведомления');
    $post->url = '/my.notification.php';
    if ($new_g != 0) {
        $post->counter = $not;
    }
}
# Друзья
if ($ank->is_friend($user) || $ank->vis_friends) {
    $res = $db->prepare("SELECT COUNT(*) FROM `friends` WHERE `id_user` = ? AND `confirm` = '1'");
    $res->execute(Array($ank->id));
    $k_friends = $res->fetchColumn();

    $post = $listing->post();
    $post->list = true;
    $post->class = 'ui segment';
    $post->icon('handshake-o');
    $post->title = __('Друзья');
    $post->url = $ank->id == $user->id ? "/my.friends.php" : "/profile.friends.php?id={$ank->id}";
    $post->counter = $k_friends;
} else {
    $post = $listing->post();
    $post->list = true;
    $post->class = 'ui segment';
    $post->icon('handshake-o');
    $post->title = __('Друзья');
    $post->url = '/faq.php?info=hide&amp;return=' . URL;
    $post->content = __('Информация скрыта');
}

# Список логинов
if ($ank->last_time_login) {
    $q = $db->query("SELECT `login` FROM `login_history` WHERE `id_user` = '$ank->id' ORDER BY `time` DESC");
    $res = $q->fetchAll();
    $logins = array();
    foreach ($res AS $v) {
        $logins[] = $v['login'];
    }
    $post = $listing->post();
    $post->list = true;
    $post->class = 'ui segment';
    $post->title = __('История логинов');
    $post->post = implode(', ', $logins);
    $post->url = '/profile.logins.php?id=' . $ank->id;
}

