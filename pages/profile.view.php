<?php

/**
 * Анкета пользователя.
 * В данном файле используются регионы (region).
 * Для корректной работы с ними рекомендую использовать PhpStorm
 */
include_once '../sys/inc/start.php';

if (AJAX) {
    $doc = new document_json();
} else {
    $doc = new document();
}

$doc->title = __('Анкета');
$doc->head = 'profile';

$ank = (empty($_GET ['id'])) ? $user : new user((int) $_GET ['id']);

if (!$ank->group) {
    $doc->access_denied(__('Нет данных'));
}

$doc->title = ($user->id && $ank->id == $user->id) ? __('Мой профиль') : __('Профиль "%s"', $ank->nick);

$doc->description = __('Профиль "%s"', $ank->nick);
$doc->keywords [] = $ank->login;

//region Предложение дружбы
if ($user->group && $ank->id && $user->id != $ank->id && isset($_GET ['friend'])) {
    // обработка действий с "другом"
    $q = $db->prepare("SELECT * FROM `friends` WHERE `id_user` = ? AND `id_friend` = ? LIMIT 1");
    $q->execute(Array($user->id, $ank->id));
    if ($friend = $q->fetch()) {
        if ($friend ['confirm']) {

            // если Вы уже являетель другом
            if (isset($_POST ['delete'])) {
                // удаляем пользователя из друзей
                $res = $db->prepare("DELETE FROM `friends` WHERE `id_user` = ? AND `id_friend` = ? OR `id_user` = ? AND `id_friend` = ?");
                $res->execute(Array($user->id, $ank->id, $ank->id, $user->id));
                $doc->msg(__('Пользователь успешно удален из друзей'));
            }
        } else {
            if (isset($_POST ['no'])) {
                // не принимаем предложение дружбы
                $res = $db->prepare("DELETE FROM `friends` WHERE `id_user` = ? AND `id_friend` = ? OR `id_user` = ? AND `id_friend` = ?");
                $res->execute(Array($user->id, $ank->id, $ank->id, $user->id));
                $res = $db->prepare("UPDATE `users` SET `friend_new_count` = `friend_new_count` - '1' WHERE `id` = ? LIMIT 1");
                $res->execute(Array($user->id));

                $doc->msg(__('Предложение дружбы отклонено'));
            } elseif (isset($_POST ['ok'])) {
                // принимаем предложение дружбы
                $res = $db->prepare("UPDATE `friends` SET `confirm` = '1', `time` = ? WHERE `id_user` = ? AND `id_friend` = ? LIMIT 1");
                $res->execute(Array(TIME, $user->id, $ank->id));
                $res = $db->prepare("UPDATE `users` SET `friend_new_count` = `friend_new_count` - '1' WHERE `id` = ? LIMIT 1");
                $res->execute(Array($user->id));
                // на всякий случай пытаемся добавить поле (хотя оно уже должно быть), если оно уже есть, то дублироваться не будет
                $res = $db->prepare("INSERT INTO `friends` (`confirm`, `id_user`, `id_friend`, `time`) VALUES ('1', ?, ?, ?)");
                $res->execute(Array($ank->id, $user->id, TIME));
                $doc->msg(__('Предложение дружбы принято'));

                # Уведомляем об подтверждении
                $ank->not("" . ($user->sex ? 'Принял' : 'Приняла') . " Ваше предложение в друзья", $user->id);
            }
        }
    } else {
        if (isset($_GET['friend']) && isset($_POST['add'])) {
            # предлагаем дружбу
            $res = $db->prepare("INSERT INTO `friends` (`confirm`, `id_user`, `id_friend`, `time`) VALUES ('0', ?, ?, ?)");
            $res->execute(Array($ank->id, $user->id, TIME));
            $res = $db->prepare("UPDATE `users` SET `friend_new_count` = `friend_new_count` + '1' WHERE `id` = ? LIMIT 1");
            $res->execute(Array($ank->id));

            $doc->msg(__('Предложение дружбы успешно отправлено'));
        }
    }
}

if ($user->group && $ank->id && $user->id != $ank->id) {
    $q = $db->prepare("SELECT * FROM `friends` WHERE `id_user` = ? AND `id_friend` = ? LIMIT 1");
    $q->execute(Array($user->id, $ank->id));
    if ($friend = $q->fetch()) {
        if ($friend['confirm']) {
            // пользователь находится в друзьях
            if (isset($_GET ['friend']) && $_GET ['friend'] == 'delete') {
                $form = new form("?id={$ank->id}&amp;friend&amp;" . passgen());
                $form->bbcode(__('Действительно хотите удалить пользователя "%s" из друзей?', $ank->login));
                $form->button(__('Да, удалить'), 'delete');
                $form->display();
            }

            if (!$ank->is_friend($user)) {
                echo "<b>" . __('Пользователь еще не подтвердил факт Вашей дружбы') . "</b><br />";
            }
            //$doc->act(__('Удалить из друзей'), "?id={$ank->id}&amp;friend=delete");
        } else {
            // пользователь не в друзьях
            $form = new form("?id={$ank->id}&amp;friend&amp;" . passgen());
            $form->bbcode(__('Пользователь "%s" предлагает Вам дружбу', $ank->login));
            $form->button(__('Принимаю'), 'ok', false);
            $form->button(__('Не принимаю'), 'no', false);
            $form->display();
        }
    } else {
        if (isset($_GET ['friend']) && $_GET ['friend'] == 'add') {
            $form = new form("?id={$ank->id}&amp;friend&amp;" . passgen());
            $form->bbcode(__('Предложить пользователю "%s" дружбу?', $ank->login));
            $form->button(__('Предложить'), 'add', false);
            $form->display();
        }
    }
}

if (!AJAX) {
    if ($user->id && $user->id != $ank->id) {
        $my_guests = $db->query("SELECT COUNT(*) FROM `my_guests` WHERE `id_ank` = '$ank->id' AND `id_user` = '$user->id' LIMIT 1")->fetchColumn();
        if ($my_guests == 0) {
            $res = $db->prepare("INSERT INTO `my_guests` (`id_ank`, `id_user`, `time`) VALUES (?, ?, ?)");
            $res->execute(Array($ank->id, $user->id, TIME));
        } else
        if ($my_guests != 0) {
            $guest = $db->query("SELECT * FROM `my_guests` WHERE `id_ank` = '$ank->id' AND `id_user` = '$user->id' LIMIT 1")->fetch();
            $res = $db->prepare("UPDATE `my_guests` SET `time` = ?, `read` = ?,`count` = `count` + ? WHERE `id` = ? LIMIT 1");
            $res->execute(Array(TIME, 1, 1, $guest['id']));
        }
    }
}

// Бан
if ($ank->is_ban) {
    $ban_listing = new listing();

    $q = $db->prepare("SELECT * FROM `ban` WHERE `id_user` = ? AND `time_start` < ? AND (`time_end` is NULL OR `time_end` > ?) ORDER BY `id` DESC");
    $q->execute(Array($ank->id, TIME, TIME));
    if ($arr = $q->fetchAll()) {
        foreach ($arr AS $c) {
            $post = $ban_listing->post();
            $adm = new user($c ['id_adm']);

            $post->title = ($adm->group <= $user->group ? '<a href="/profile.view.php?id=' . $adm->id . '">' . $adm->nick . '</a>: ' : '') . text::toValue($c ['code']);


            if ($c ['time_start'] && TIME < $c ['time_start']) {
                $post->content[] = '[b]' . __('Начало действия') . ':[/b]' . misc::when($c ['time_start']) . "\n";
            }
            if ($c['time_end'] === NULL) {
                $post->content[] = '[b]' . __('Пожизненная блокировка') . "[/b]\n";
            } elseif (TIME < $c['time_end']) {
                $post->content[] = __('Осталось: %s', misc::when($c['time_end'])) . "\n";
            }
            if ($c['link']) {
                $post->content[] = __('Ссылка на нарушение: %s', $c['link']) . "\n";
            }

            $post->content[] = __('Комментарий: %s', $c['comment']) . "\n";
        }
    }
    $ban_listing->display();
}

if (isset($_GET['like_avatar']) && $user->id) {
    $res = $db->query("SELECT * FROM `avatar_like` WHERE `id_user` = '" . intval($user->id) . "' AND `id_avatar` = '$ank->id' LIMIT 1")->fetch();
    if (!$res) {
        $res = $db->prepare("INSERT INTO `avatar_like` (`id_user`, `time`, `id_avatar`) VALUES (?, ?, ?)");
        $res->execute(Array(intval($user->id), TIME, $ank->id));
        $ank->not("" . ($user->sex ? 'Оценил' : 'Оценила') . " Ваше [url=/avatar.comments.php?id=" . $ank->id . "]фото профиля[/url]", $user->id);
        $doc->msg(__('Вы успешно оценили фото'));
        header('Refresh: 1; url=/profile.view.php?id=' . $ank->id);
    } else {
        $doc->err(__('Вы уже оценивали это фото'));
        header('Refresh: 1; url=/profile.view.php?id=' . $ank->id);
    }
}

// Профиль пользователя
$listing = new listing();
$fon = new user_fon($ank->id);
$d = new design();
$d->assign('fon', $fon->image());
$d->assign('avatar', array($ank->getAvatar($dcms->browser_type == 'full' ? '150' : '100'), null));
if ($user->id == $ank->id) {
    $d->assign('fon_create', array('/my.fon.php', '<i class="fa fa-camera"></i>')); // Кнопка добавить фон
    //$d->assign('avatar_create', array('/my.avatar.php', '<i class="fa fa-refresh"></i>'));
}

if ($ank->avatar == 1) {
    $likeCount = $db->query("SELECT COUNT(*) FROM `avatar_like` WHERE `id_avatar` = '$ank->id' ")->fetchColumn();
    $like = $db->query("SELECT * FROM `avatar_like` WHERE `id_user` = '$user->id' AND `id_avatar` = '$ank->id' LIMIT 1")->fetch();
    if ($user->id && $user->id != $ank->id && !$like) {
        $d->assign('like_avatar', array('?id=' . $ank->id . '&amp;like', '<i class="fa fa-heart-o fa-fw"></i>' . __('%s', $likeCount)));
    } elseif ($user->id) {
        $d->assign('like_all_avatar', array('/avatar.like.php?id=' . $ank->id . '', '<i class="fa fa-heart fa-fw"></i>' . __('%s', $likeCount)));
    }
    $res = $db->query("SELECT COUNT(*) FROM `avatar_komm` WHERE `id_avatar` = '$ank->id'");
    $cca = $res->fetchColumn();
    $d->assign('comments_avatar', array('/avatar.comments.php?id=' . $ank->id . '', '<i class="fa fa-comments-o fa-fw"></i>' . __('%s', $cca)));
}

if ($user->group > 0 & ($ank->id != $user->id)) {
    $d->assign('gifts', array('/presents/?user=' . $ank->id . '', '<i class="fa fa-gift fa-fw"></i>' . __('Подарить подарок')));
    $d->assign('mess', array('my.mail.php?id=' . $ank->id . '', '<i class="fa fa-envelope-o fa-fw"></i>' . __('Сообщение')));
    $d->assign('balls', array('transfer.points.php?id=' . $ank->id . '', '<i class="fa fa-gg-circle fa-fw"></i>' . __('Передать баллы')));
    if (!$friend['confirm']) {
        $d->assign('friend', array('?id=' . $ank->id . '&amp;friend=add', '<i class="fa fa-user-plus fa-fw"></i>' . __('Добавить в друзья')));
    } else {
        $d->assign('friend', array('?id=' . $ank->id . '&amp;friend=delete', '<i class="fa fa-user-times fa-fw"></i>' . __('Удалить из друзей')));
    }
} elseif ($user->group > 0 & ($ank->id == $user->id)) {
    $d->assign('profile_edit', array('/profile.edit.php', '<i class="fa fa-edit fa-fw"></i>' . __('Редактировать анкету')));
}

if (!$user->vk_id) {
    $d->assign('login', $ank->nick);
} else {
    $d->assign('login', $ank->nick . ' ' . $ank->patronymic . '');
}
if ($ank->online) {
    $d->assign('online', array('on', '<i class="fa fa-circle fa-fw"></i>'));
} else {
    $d->assign('online', array('off', '<i class="fa fa-circle fa-fw"></i>'));
}
if ($ank->group > 1) {
    $d->assign('group_name', $ank->group_name);
} elseif ($ank->is_vip) {
    $d->assign('group_name', '<i class="fa fa-circle fa-fw"></i> VIP');
}

$d->display('design.profile.tpl');

$act = (isset($_GET['act'])) ? htmlspecialchars($_GET['act']) : null;

switch ($act) {
    default:
        require_once 'profile.php';
        break;

    case 'anketa':
        require_once 'anketa.php';
        break;

    case 'activity':
        require_once 'activity.php';
        break;
}
//endregion
//region По приглашению от...

$q = $db->prepare("SELECT `id_user` FROM `invations` WHERE `id_invite` = ? LIMIT 1");
$q->execute(Array($ank->id));
if ($row = $q->fetch()) {
    $inv = new user($row['id_user']);
    $post = $listing->post();
    $post->title = text::toOutput(__('По приглашению от %s', '[user]' . $inv->id . '[/user]'));
}
//endregion
$listing->display();
//endregion

if ($user->group && $ank->id != $user->id) {
    if ($user->group > $ank->group) {
        $doc->opt(__('Доступные действия'), "/dpanel/user.actions.php?id={$ank->id}");
    }
}

if ($user->group) {
    $doc->ret(__('Личное меню'), '/menu.user.php');
}    