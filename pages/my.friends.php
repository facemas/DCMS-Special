<?php

# DCMS Special
# Модификация densnet

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Мои друзья');

# Входящие заявки
$res = $db->prepare("SELECT COUNT(*) FROM `friends` WHERE `id_user` = ? AND `confirm` = '0'");
$res->execute(Array($user->id));
$user->friend_new_count = $res->fetchColumn();
$application = ((!$user->friend_new_count) ? null : $user->friend_new_count);

# Исходящие заявки
$res = $db->prepare("SELECT COUNT(*) FROM `friends` WHERE `id_friend` = ? AND `confirm` = '0'");
$res->execute(Array($user->id));
$res = $res->fetchColumn();
$applicationOut = (!$res ? '' : $res);

# Общее кол-во друзей
$res = $db->prepare("SELECT COUNT(*) FROM `friends` WHERE `id_user` = ? AND `confirm` = '1'");
$res->execute(Array($user->id));
$res = $res->fetchColumn();
$friendsCount = (!$res ? '' : $res);

# Общее кол-во друзей онлайн
$res = $db->prepare("SELECT COUNT(*) FROM `friends` INNER JOIN `users_online` ON `friends`.`id_friend`=`users_online`.`id_user` WHERE `friends`.`id_user` = ? ORDER BY `users_online`.`time_login` ASC, `time`");
$res->execute(Array($user->id));
$res = $res->fetchColumn();
$friendsOnline = (!$res ? '' : $res);

$anks = (empty($_GET['id'])) ? $user : new user((int) $_GET['id']);

if ($user->group && $anks->id && $user->id != $anks->id && isset($_GET['friend'])) {
    // обработка действий с "другом"
    $q = $db->prepare("SELECT * FROM `friends` WHERE `id_user` = ? AND `id_friend` = ? LIMIT 1");
    $q->execute(Array($user->id, $anks->id));
    if ($friend = $q->fetch()) {
        if ($friend['confirm']) {

            if (isset($_POST['delete'])) {
                // удаляем пользователя из друзей
                $res = $db->prepare("DELETE FROM `friends` WHERE `id_user` = ? AND `id_friend` = ? OR `id_user` = ? AND `id_friend` = ?");
                $res->execute(Array($user->id, $anks->id, $anks->id, $user->id));
                $doc->msg(__('Пользователь успешно удален из друзей'));
            }
            if (isset($_POST['cancel'])) {
                // отменяем заявку на добавления в друзья
                $res = $db->prepare("DELETE FROM `friends` WHERE `id_user` = ? AND `id_friend` = ? OR `id_user` = ? AND `id_friend` = ?");
                $res->execute(Array($user->id, $anks->id, $anks->id, $user->id));
                $doc->msg(__('Заявка успешно отменена'));
            }
        } else {
            if (isset($_GET['no'])) {
                // не принимаем предложение дружбы
                $res = $db->prepare("DELETE FROM `friends` WHERE `id_user` = ? AND `id_friend` = ? OR `id_user` = ? AND `id_friend` = ?");
                $res->execute(Array($user->id, $anks->id, $anks->id, $user->id));
                $res = $db->prepare("UPDATE `users` SET `friend_new_count` = `friend_new_count` - '1' WHERE `id` = ? LIMIT 1");
                $res->execute(Array($user->id));

                $doc->msg(__('Предложение дружбы отклонено'));
            } elseif (isset($_GET['ok'])) {
                // принимаем предложение дружбы
                $res = $db->prepare("UPDATE `friends` SET `confirm` = '1', `time` = ? WHERE `id_user` = ? AND `id_friend` = ? LIMIT 1");
                $res->execute(Array(TIME, $user->id, $anks->id));
                $res = $db->prepare("UPDATE `users` SET `friend_new_count` = `friend_new_count` - '1' WHERE `id` = ? LIMIT 1");
                $res->execute(Array($user->id));
                // на всякий случай пытаемся добавить поле (хотя оно уже должно быть), если оно уже есть, то дублироваться не будет
                $res = $db->prepare("INSERT INTO `friends` (`confirm`, `id_user`, `id_friend`, `time`) VALUES ('1', ?, ?, ?)");
                $res->execute(Array($anks->id, $user->id, TIME));

                # Уведомляем об подтверждении
                $anks->not("" . ($user->sex ? 'Принял' : 'Приняла') . " Ваше предложение в друзья", $user->id);

                $doc->msg(__('Предложение дружбы принято'));
            }
        }
    }
}

if ($user->group && $anks->id && $user->id != $anks->id) {
    $q = $db->prepare("SELECT * FROM `friends` WHERE `id_user` = ? AND `id_friend` = ? LIMIT 1");
    $q->execute(Array($user->id, $anks->id));
    if ($friend = $q->fetch()) {
        if ($friend['confirm']) {
            // пользователь находится в друзьях
            if (isset($_GET ['friend']) && $_GET ['friend'] == 'delete') {
                $form = new form("?id={$anks->id}&amp;friend&amp;" . passgen());
                $form->bbcode(__('Действительно хотите удалить пользователя "%s" из друзей?', $anks->login));
                $form->button(__('Да, удалить'), 'delete');
                $form->display();
            }
            if (isset($_GET ['friend']) && $_GET ['friend'] == 'cancel') {
                $form = new form("?id={$anks->id}&amp;friend&amp;" . passgen());
                $form->bbcode(__('Действительно хотите отменить заявку?'));
                $form->button(__('Да, отменить'), 'cancel');
                $form->display();
            }
        }
    }
}

$act = (isset($_GET['act'])) ? htmlspecialchars($_GET['act']) : null;

switch ($act) {
    # Выводим список друзей
    default:

        $pages = new pages;
        $res = $db->prepare("SELECT COUNT(*) FROM `friends` WHERE `id_user` = ?");
        $res->execute(Array($user->id));
        $pages->posts = $res->fetchColumn();

        $q = $db->prepare("SELECT * FROM `friends` WHERE `id_user` = ? ORDER BY `confirm` ASC, `time` DESC LIMIT " . $pages->limit . ";");
        $q->execute(Array($user->id));

        $from = 'all';
        $doc->tab(__('Заявки %s', $application), '?act=application', $from === 'application');
        $doc->tab(__('Онлайн %s', $friendsOnline), '?act=online', $from === 'online');
        $doc->tab(__('Все %s', $friendsCount), '?', $from === 'all');

        $listing = new listing();

        while ($friend = $q->fetch()) {
            $ank = new user($friend['id_friend']);
            if ($friend['confirm'] && $ank->is_friend($user)) {

                $post = $listing->post();

                $post->url = '?act=action&amp;id=' . $ank->id;
                $post->title = $ank->nick();
                $post->time = misc::when($friend['time']);
                $post->image = $ank->getAvatar();
                $post->post = "<small style='color: grey;'>" . __('Нажмите для действий') . "</small>";
            }
        }
        $listing->display(__('Друзей нет'));

        break;

    # Друзья онлайн
    case 'online':

        $pages = new pages;
        $res = $db->prepare("SELECT COUNT(*) FROM `friends` INNER JOIN `users_online` ON `friends`.`id_friend`=`users_online`.`id_user` WHERE `friends`.`id_user` = ?");
        $res->execute(Array($user->id));
        $pages->posts = $res->fetchColumn();

        $q = $db->prepare("SELECT * FROM `friends` INNER JOIN `users_online` ON `friends`.`id_friend`=`users_online`.`id_user` WHERE `friends`.`id_user` = ? ORDER BY `users_online`.`time_login` ASC, `time` DESC LIMIT " . $pages->limit);
        $q->execute(Array($user->id));

        $from = 'online';
        $doc->tab(__('Заявки %s', $application), '?act=application', $from === 'application');
        $doc->tab(__('Онлайн %s', $friendsOnline), '?act=online', $from === 'online');
        $doc->tab(__('Все %s', $friendsCount), '?', $from === 'all');

        $listing = new listing();


        while ($friend = $q->fetch()) {
            $ank = new user($friend['id_friend']);
            if ($friend['confirm'] && $ank->is_friend($user)) {

                $post = $listing->post();

                $post->url = '?act=action&amp;id=' . $ank->id;
                $post->title = $ank->nick();

                $post->image = $ank->getAvatar();
                $post->post = "<small style='color: grey;'>" . __('Нажмите для действий') . "</small>";
            }
        }
        $listing->display(__('Друзей нет онлайн'));

        break;

    #Действие над друзьями
    case 'action':

        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $doc->toReturn('./');
            $doc->err(__('Ошибка выбора пользователя'));
            exit();
        }

        $pages = new pages;
        $res = $db->prepare("SELECT COUNT(*) FROM `friends` WHERE `id_user` = ?");
        $res->execute(Array($user->id));
        $pages->posts = $res->fetchColumn();

        $q = $db->prepare("SELECT * FROM `friends` WHERE `id_user` = ? ORDER BY `confirm` ASC, `time` DESC LIMIT " . $pages->limit . ";");
        $q->execute(Array($user->id));

        $from = 'action';
        $doc->tab($anks->login, "/profile.view.php?id=$anks->id", $from === 'action');
        $doc->tab(__('Заявки %s', $application), '?act=application', $from === 'application');
        $doc->tab(__('Онлайн %s', $friendsOnline), '?act=online', $from === 'online');
        $doc->tab(__('Все %s', $friendsCount), '?', $from === 'all');

        $listing = new listing;

        $post = $listing->post();
        $post->title = $anks->nick();
        if (!$anks->is_friend($user)) {
            $post->post = __('Ожидается подтверждение');
        }
        $post->image = $anks->getAvatar();

        $post = $listing->post();
        $post->title = __('Посмотреть профиль');
        $post->icon('vcard-o');
        $post->url = '/profile.view.php?id=' . $anks->id;


        if ($user->group) {
            $post = $listing->post();
            $post->title = __('Написать сообщение');
            $post->icon('envelope');
            $post->url = "/my.mail.php?id=$anks->id";
        }

        if ($user->group && $anks->id && $user->id != $anks->id) {
            $q = $db->prepare("SELECT * FROM `friends` WHERE `id_user` = ? AND `id_friend` = ? LIMIT 1");
            $q->execute(Array($user->id, $anks->id));

            if ($friend = $q->fetch()) {
                $ank = new user($friend['id_friend']);
                $ank2 = new user($friend['id_user']);

                if (!$friend['confirm'] && !$ank2->is_friend($user)) {
                    $post = $listing->post();
                    $post->title = __('Отменить заявку');
                    $post->icon('times');
                    $post->url = "?id=$anks->id&amp;friend=cancel";
                } elseif ($friend['confirm'] && $ank2->is_friend($user)) {
                    $post = $listing->post();
                    $post->title = __('Удалить из друзей');
                    $post->icon('trash-o');
                    $post->url = "?id=$anks->id&amp;friend=delete";
                } else {

                    $post = $listing->post();
                    $post->title = __('Принимаю');
                    $post->icon('check');
                    $post->url = "?id=$anks->id&amp;friend&amp;ok";

                    $post = $listing->post();
                    $post->title = __('Отклонить');
                    $post->icon('times');
                    $post->url = "?id=$anks->id&amp;friend&amp;no";
                }
            }
        }

        $listing->display();

        break;

    # Заявки в друзья
    case 'application':

        $from = 'application';
        $doc->tab(__('Заявки %s', $application), '?act=application', $from === 'application');
        $doc->tab(__('Онлайн %s', $friendsOnline), '?act=online', $from === 'online');
        $doc->tab(__('Все %s', $friendsCount), '?', $from === 'all');

        $r = (isset($_GET['friend'])) ? htmlspecialchars($_GET['friend']) : null;
        switch ($r) {

            # Входящие заявки в друзья
            default:
                $pages = new pages;
                $res = $db->prepare("SELECT COUNT(*) FROM `friends` WHERE `id_user` = ?");
                $res->execute(Array($user->id));
                $pages->posts = $res->fetchColumn();

                echo "<div id='tabs'>";
                echo "<a class='tab sel1'>" . __('Входящие') . " $application</a> ";
                echo "<a class='tab sel' href='?act=application&amp;friend=outbox'>" . __('Исходящие') . " $applicationOut</a> ";
                echo "</div>";

                $q = $db->prepare("SELECT * FROM `friends` WHERE `id_user` = ? AND `confirm` = '0' ORDER BY `time` DESC LIMIT " . $pages->limit . ";");
                $q->execute(Array($user->id));

                $listing = new listing();
                while ($friend = $q->fetch()) {
                    $ank = new user($friend['id_friend']);

                    $post = $listing->post();
                    $post->url = '?act=action&amp;id=' . $ank->id;
                    $post->title = $ank->nick();
                    $post->image = $ank->getAvatar();
                    $post->time = misc::when($friend['time']);
                    $post->content = __('Хочет быть Вашим другом');
                }
                $listing->display(__('Нет заявок'));

                break;

            # Исходящие заявки в друзья
            case 'outbox':
                $pages = new pages;
                $res = $db->prepare("SELECT COUNT(*) FROM `friends` WHERE `id_user` = ?");
                $res->execute(Array($user->id));
                $pages->posts = $res->fetchColumn();

                echo "<div id='tabs'>";
                echo "<a class='tab sel' href='?act=application&amp;friend'>" . __('Входящие') . " $application</a> ";
                echo "<a class='tab sel1'>" . __('Исходящие') . " $applicationOut</a> ";
                echo "</div>";

                $q = $db->prepare("SELECT * FROM `friends` WHERE `id_friend` = ? AND `confirm` = '0' ORDER BY `time` DESC LIMIT " . $pages->limit . ";");
                $q->execute(Array($user->id));

                $listing = new listing();
                while ($friend = $q->fetch()) {
                    $ank = new user($friend['id_user']);

                    $post = $listing->post();
                    $post->url = '?act=action&amp;id=' . $ank->id;
                    $post->title = $ank->nick();
                    $post->image = $ank->getAvatar();
                    $post->time = misc::when($friend['time']);
                    $post->content = __('Вы отправили заявку');
                }

                $listing->display(__('Нет заявок'));

                break;
        }

        break;
}

$pages->display('?');

$doc->ret(__('Личное меню'), '/menu.user.php');
