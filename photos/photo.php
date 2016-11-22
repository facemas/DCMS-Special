<?php

include_once '../sys/inc/start.php';
if (AJAX) {
    $doc = new document_json();
} else {
    $doc = new document();
}
$doc->title = __('Фотоальбомы');

if (!empty($_GET ['id'])) {
    $ank = new user((int) $_GET ['id']);
} else {
    $ank = $user;
}

if (!$ank->group) {
    $doc->access_denied(__('Ошибка пользователя'));
}

// папка фотоальбомов пользователей
$photos = new files(FILES . '/.photos');
// папка альбомов пользователя
$albums_path = FILES . '/.photos/' . $ank->id;

if (!@is_dir($albums_path)) {
    if (!$albums_dir = $photos->mkdir($ank->login, $ank->id))
        $doc->access_denied(__('Не удалось создать папку под фотоальбомы пользователя'));

    $albums_dir->id_user = $ank->id;
    $albums_dir->group_show = 0;
    $albums_dir->group_write = min($ank->group, 2);
    $albums_dir->group_edit = max($ank->group, 4);
    unset($albums_dir);
}

$albums_dir = new files($albums_path);

if (empty($_GET['album']) || !$albums_dir->is_dir($_GET['album'])) {
    $doc->err(__('Запрошеный альбом не существует'));
    $doc->ret(__('К альбомам'), 'albums.php?id=' . $ank->id);
    header('Refresh: 1; url=albums.php?id=' . $ank->id);
    exit();
}

$album_name = (string) $_GET['album'];
$album = new files($albums_path . '/' . $album_name);
$doc->title = $album->runame;

if (empty($_GET['photo']) || !$album->is_file($_GET['photo'])) {
    $doc->err(__('Запрошенная фотография не найдена'));
    $doc->ret(__('К альбому %s', $name), 'photos.php?id=' . $ank->id . '&amp;album=' . urlencode($album->name));
    $doc->ret(__('К альбомам'), 'albums.php?id=' . $ank->id);
    header('Refresh: 1; url=photos.php?id=' . $ank->id . '&alnum=' . urlencode($album->name));
    exit();
}

$photo_name = $_GET['photo'];

$photo = new files_file($albums_path . '/' . $album_name, $photo_name);
$doc->title = $photo->runame;

$doc->description = __('Фото пользователя %s:%s', $ank->login, $photo->runame);
$doc->keywords [] = $photo->runame;
$doc->keywords [] = $album->runame;
$doc->keywords [] = $ank->login;

// удаление фотографии
if ($photo->id_user && $photo->id_user == $user->id) {
    if (!empty($_GET ['act']) && $_GET ['act'] === 'delete') {

        if (!empty($_POST ['delete'])) {
            if ($photo->delete()) {
                $doc->msg(__('Фото успешно удалено'));
                $doc->ret(__('Альбом %s', $album->name), 'photos.php?id=' . $ank->id . '&amp;album=' . urlencode($album->name));
                $doc->ret(__('Альбомы %s', $ank->nick), 'albums.php?id=' . $ank->id);
                header('Refresh: 1; url=photos.php?id=' . $ank->id . '&album=' . urlencode($album->name) . '&' . passgen());
                exit();
            } else {

                $doc->err(__('Не удалось удалить фото'));
                $doc->ret(__('К фото'), '?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;photo=' . urlencode($photo->name));
                $doc->ret(__('Альбом %s', $album->name), 'photos.php?id=' . $ank->id . '&amp;album=' . urlencode($album->name));
                $doc->ret(__('Альбомы %s', $ank->login), 'albums.php?id=' . $ank->id);
                header('Refresh: 1; url=?id=' . $ank->id . '&album=' . urlencode($album->name) . '&photo=' . urlencode($photo->name) . '&' . passgen());
            }
            exit();
        }

        $form = new form(new url(null, array('id' => $ank->id, 'album' => $album->name, 'photo' => $photo->name)));
        $form->block('<div class="ui mini yellow message">' . __('Вы действительно хотите удалить фото?') . '</div>');
        $form->block('<input type="submit" name="delete" value="' . __('Удалить фото') . '" class="tiny ui blue button" />');
        $form->display();

        $doc->ret(__('К фото'), '?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;photo=' . urlencode($photo->name));
        $doc->ret(__('Альбом %s', $album->name), 'photos.php?id=' . $ank->id . '&amp;album=' . urlencode($album->name));
        $doc->ret(__('Альбомы %s', $ank->login), 'albums.php?id=' . $ank->id);
        exit();
    }

    $doc->opt(__('Удалить фото'), '?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;photo=' . urlencode($photo->name) . '&amp;act=delete');
}

if ($screen = $photo->getScreen($doc->img_max_width(), 0)) {
    echo "<img class='photo' src='" . $screen . "' alt='" . __('Фото') . " " . text::toValue($photo->runame) . "' /><br />\n";
}

$pages = new pages ();
$res = $db->prepare("SELECT COUNT(*) FROM `files_comments` WHERE `id_file` = ?");
$res->execute(Array($photo->id));
$pages->posts = $res->fetchColumn(); // количество сообщений

$can_write = true;
if (!$user->is_writeable) {
    $doc->err(__('Новым пользователям разрешено писать только через %s часа пребывания на сайте', $dcms->user_write_limit_hour));
    $can_write = false;
}

if ($can_write && $pages->this_page == 1) {

    if (isset($_POST['send']) && isset($_POST['message']) && isset($_POST['token']) && $user->group) {
        $message = (string) $_POST['message'];
        $users_in_message = text::nickSearch($message);
        $message = text::input_text($message);

        if (!antiflood::useToken($_POST['token'], 'files')) {
            // нет токена (обычно, повторная отправка формы)
        } elseif ($dcms->censure && $mat = is_valid::mat($message)) {
            $doc->err(__('Обнаружен мат: %s', $mat));
        } elseif ($message) {
            $user->balls += $dcms->add_balls_comment_file;
            $res = $db->prepare("INSERT INTO `files_comments` (`id_file`, `id_user`, `time`, `text`) VALUES (?,?,?,?)");
            $res->execute(Array($photo->id, $user->id, TIME, $message));
            header('Refresh: 1; url=?' . passgen() . '&' . SID);
            $doc->ret(__('Вернуться'), '?' . passgen());
            $doc->msg(__('Комментарий успешно оставлен'));

            $id_message = $db->lastInsertId();
            if ($users_in_message) {
                for ($i = 0; $i < count($users_in_message) && $i < 20; $i++) {
                    $user_id_in_message = $users_in_message[$i];
                    if ($user_id_in_message == $user->id || ($photo->id_user && $photo->id_user == $user_id_in_message)) {
                        continue;
                    }
                    $ank_in_message = new user($user_id_in_message);
                    if ($ank_in_message->notice_mention) {
                        $ank_in_message->not(($user->sex ? 'упомянул' : 'упомянула') . " о вас в комментарии к фото [url=/photos/photo.php?id=$ank->id&album=$album->name&photo=$photo->name]$photo->runame[/url]", $user->id);
                    }
                }
            }

            $photo->comments++;

            if ($photo->id_user && $photo->id_user != $user->id) { // уведомляем автора о комментарии
                $ank = new user($photo->id_user);
                $ank->not(($user->sex ? 'оставил' : 'оставила') . " комментарий к вашему фото [url=/photos/photo.php?id=$ank->id&album=$album->name&photo=$photo->name]{$photo->runame}[/url]", $user->id);
            }
            if ($doc instanceof document_json) {
                $doc->form_value('message', '');
                $doc->form_value('token', antiflood::getToken('files'));
            }

            exit;
        } else {
            $doc->err(__('Сообщение пусто'));
        }

        if ($doc instanceof document_json) {
            $doc->form_value('token', antiflood::getToken('files'));
        }
    }

    if ($user->group) {
// форма добавления комментария
        $message_form = '';
        if (isset($_GET['message']) && is_numeric($_GET['message'])) {
            $id_message = (int) $_GET['message'];
            $q = $db->prepare("SELECT * FROM `files_comments` WHERE `id` = ? LIMIT 1");
            $q->execute(Array($id_message));
            if ($message = $q->fetch()) {
                $anks = new user($message['id_user']);
                if (isset($_GET['reply'])) {
                    $message_form = '@' . $anks->login . ',';
                } elseif (isset($_GET['quote'])) {
                    $message_form = "[quote id_user=\"{$anks->id}\" time=\"{$message['time']}\"]{$message['text']}[/quote]";
                }
            }
        }

        if (!AJAX) {
            $form = new form("?id=$ank->id&amp;album=$album->name&amp;photo=$photo->name");
            $form->refresh_url("?id=$ank->id&amp;album=$album->name&amp;photo=$photo->name");
            $form->setAjaxUrl("?id=$ank->id&amp;album=$album->name&amp;photo=$photo->name");
            $form->hidden('token', antiflood::getToken('files'));
            $form->textarea('message', __('Комментарий'), $message_form, true);
            $form->button(__('Отправить'), 'send', false);
            $form->display();
        }
    }
}
if (!empty($_GET ['delete_comm']) && $user->group >= $photo->group_edit) {
    $delete_comm = (int) $_GET['delete_comm'];
    $res = $db->prepare("SELECT COUNT(*) FROM `files_comments` WHERE `id` = ? AND `id_file` = ? LIMIT 1");
    $res->execute(Array($delete_comm, $photo->id));
    $k = $res->fetchColumn();

    if ($k) {
        $res = $db->prepare("DELETE FROM `files_comments` WHERE `id` = ? LIMIT 1");
        $res->execute(Array($delete_comm));
        $photo->comments--;
        $doc->msg(__('Комментарий успешно удален'));
    } else {
        $doc->err(__('Комментарий уже удален'));
    }
}

$listing = new ui_components();
$listing->ui_comment = true; //подключаем css comments
$listing->ui_segment = true; //подключаем css segments
$listing->class = $dcms->browser_type == 'full' ? 'segments minimal large comments' : 'segments small comments';


if (!empty($form)) {
    $listing->setForm($form);
}

$q = $db->prepare("SELECT * FROM `files_comments` WHERE `id_file` = ? ORDER BY `id` DESC LIMIT " . $pages->limit);
$q->execute(Array($photo->id));
$after_id = false;
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $comment) {
        $ank2 = new user($comment['id_user']);
        $post = $listing->post();
        $post->class = 'ui segment comment';
        $post->comments = true;
        $post->id = 'photo_post_' . $comment['id'];
        $post->login = $ank2->nick();
        $post->time = misc::when($comment['time']);
        $post->avatar = $ank2->getAvatar();
        $post->image_a_class = 'ui avatar';
        $post->content = text::toOutput($comment['text']);

        if ($user->group && ($user->id != $ank2->id)) {
            $post->action(false, '?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;photo=' . urlencode($photo->name) . '&amp;message=' . $comment['id'] . '&amp;reply', __('Ответить'));
        }
        if ($user->group) {
            $post->action(false, '?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;photo=' . urlencode($photo->name) . '&amp;message=' . $comment['id'] . '&amp;quote', __('Цитировать'));
        }
        if ($user->group >= $photo->group_edit) {
            $post->action(false, "?id=$ank->id&amp;album=" . urlencode($album->name) . "&amp;photo=" . urlencode($photo->name) . "&amp;delete_comm=$comment[id]", __('Удалить'));
        }

        if ($doc instanceof document_json) {
            $doc->add_post($post, $after_id);
        }

        $after_id = $post->id;
    }
}

if ($doc instanceof document_json && !$arr) {
    $post = new listing_post(__('Нет результатов'));
    $post->icon('clone');
    $doc->add_post($post);
}

$listing->setAjaxUrl('?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;photo=' . urlencode($photo->name) . '&amp;page=' . $pages->this_page);
$listing->display(__('Нет результатов'));
$pages->display('?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;photo=' . urlencode($photo->name) . '&amp;'); // вывод страниц

if ($doc instanceof document_json) {
    $doc->set_pages($pages);
}

$doc->ret(__('Альбом %s', $album->runame), 'photos.php?id=' . $ank->id . '&amp;album=' . urlencode($album->name));
$doc->ret(__('Альбомы %s', $ank->login), 'albums.php?id=' . $ank->id);
