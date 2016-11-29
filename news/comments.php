<?php

include_once '../sys/inc/start.php';

if (AJAX) {
    $doc = new document_json();
} else {
    $doc = new document();
}

$doc->title = __('Комментарии к новости');
$doc->ret(__('Все новости'), './');

$id = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `news` WHERE `id` = ? LIMIT 1");
$q->execute(Array($id));

if (!$news = $q->fetch()) {
    $doc->access_denied(__('Новость не найдена или удалена'));
}

if ($user->group) {
    $q = $db->prepare("SELECT * FROM `news_views` WHERE `id_news` = ? AND `id_user` = ? AND `time` > ?");
    $q->execute(Array($news['id'], $user->id, DAY_TIME));
    if (!$q->fetch()) {
        $res = $db->prepare("INSERT INTO `news_views` (`id_news`, `id_user`, `time`) VALUES (?, ?, ?)");
        $res->execute(Array($news['id'], $user->id, (TIME + 1)));
    } else {
        $res = $db->prepare("UPDATE `news_views` SET `time` = ? WHERE `id_news` = ? AND `id_user` = ? ORDER BY `time` DESC LIMIT 1");
        $res->execute(Array((TIME + 1), $news['id'], $user->id));
    }
}

$like = $db->query("SELECT * FROM `news_like` WHERE `id_news` = '" . intval($news['id']) . "'")->fetchAll();

if (isset($_GET['like']) && $user->id) {
    $doc->toReturn(new url('/news/comments.php?id=' . $news['id']));
    $qq = $db->query("SELECT * FROM `news_like` WHERE `id_user` = '" . intval($user->id) . "' AND `id_news` = '" . intval($news['id']) . "' LIMIT 1")->fetch();

    if (!$qq) {
        $res = $db->prepare("INSERT INTO `news_like` (`id_user`, `time`, `id_news`) VALUES (?, ?, ?)");
        $res->execute(Array(intval($user->id), TIME, intval($news['id'])));
        $doc->msg(__('Вам понравилась новость'));

        if (isset($_GET['return'])) {
            $doc->ret('Вернуться', text::toValue($_GET['return']));
        }
    } else {
        $doc->err(__('Вы уже оценивали эту новость'));
        if (isset($_GET['return'])) {
            $doc->ret('Вернуться', text::toValue($_GET['return']));
        }
    }
}

if (isset($_GET['likes']) && $user->id) {
    $doc->toReturn(new url('/news/index.php?'));
    $qq = $db->query("SELECT * FROM `news_like` WHERE `id_user` = '" . intval($user->id) . "' AND `id_news` = '" . intval($news['id']) . "' LIMIT 1")->fetch();

    if (!$qq) {
        $res = $db->prepare("INSERT INTO `news_like` (`id_user`, `time`, `id_news`) VALUES (?, ?, ?)");
        $res->execute(Array(intval($user->id), TIME, intval($news['id'])));
        $doc->msg(__('Вам понравилась новость'));

        if (isset($_GET['return'])) {
            $doc->ret('Вернуться', text::toValue($_GET['return']));
        }
    } else {
        $doc->err(__('Вы уже оценивали эту новость'));
        if (isset($_GET['return'])) {
            $doc->ret('Вернуться', text::toValue($_GET['return']));
        }
    }
}

$ank = new user((int) $news['id_user']);

if ($user->group >= max($ank->group, 4)) {
    $listing = new ui_components();
    $listing->ui_menu = true;

    $post = $listing->post();
    $post->head = '<div class="ui icon menu">
        ' . (!$news['id_vote'] ? '
        <span data-tooltip="' . __('Создать голосование') . '" data-position="bottom left">
        <a class="item" href="vote.new.php?id=' . $news['id'] . '"><i class="fa fa-bar-chart fa-fw"></i></a>
            </span>' : '
        <span data-tooltip="' . __('Редактировать голосование') . '" data-position="bottom left">
        <a class="item" href="vote.edit.php?id=' . $news['id'] . '"><i class="fa fa-bar-chart fa-fw"></i></a>
            </span>') . '
            <span data-tooltip="' . __('Редактировать новость') . '" data-position="bottom left">
        <a class="item" href="news.edit.php?id=' . $news['id'] . '"><i class="fa fa-cog fa-fw"></i></a>
            </span>
            <span data-tooltip="' . __('Удалить новость') . '" data-position="bottom left">
        <a class="item" href="news.delete.php?id=' . $news['id'] . '"><i class="fa fa-trash-o fa-fw"></i></a>
            </span>
            ' . (!$news['sended'] ? '
            <span data-tooltip="' . __('Рассылка') . '" data-position="bottom left">
        <a class="item" href="news.send.php?id=' . $news['id'] . '"><i class="fa fa-send-o fa-fw"></i></a>
            </span>' : null) . '
        </div>';

    $listing->display();
}

$listing = new ui_components();
$listing->ui_comment = true; //подключаем css comments
$listing->ui_segment = true; //подключаем css segment
$listing->ui_list = true; //подключаем css segment
$listing->class = $dcms->browser_type == 'full' ? 'segments minimal comments' : 'segments comments';

$post = $listing->post();

$post->class = 'ui segment comment';
$post->comments = true;
$ank = new user((int) $news['id_user']);

$post->icon('feed');
$post->title = text::toValue($news['title']);
$post->content = text::toOutput($news['text']);
$post->url = 'comments.php?id=' . $news['id'];
$post->time = misc::times($news['time']);

# Счетчик комментариев
$res = $db->prepare("SELECT COUNT(*) FROM `news_comments` WHERE `id_news` = ?");
$res->execute(Array(intval($news['id'])));
$comments = $res->fetchColumn();
# Счетчик просмотров
$res = $db->prepare("SELECT COUNT(*) FROM `news_views` WHERE `id_news` = ?");
$res->execute(Array(intval($news['id'])));
$views = $res->fetchColumn();
# Счетчик лайков
$res = $db->prepare("SELECT COUNT(*) FROM `news_like` WHERE `id_news` = ?");
$res->execute(Array(intval($news['id'])));
$like = $res->fetchColumn();

$post = $listing->post();

$post->class = 'ui segment comment';
$post->bottom .= '<div class="ui very relaxed horizontal list"> ';

# Комментарии
$post->bottom .= '<div class="item"><div class="content"><a href="comments.php?id=' . $news['id'] . '" class="header" data-tooltip="' . __('Комментариев %s', $comments) . '" data-position="top left"><i class="fa fa-comments fa-fw"></i> ' . __('%s', $comments) . '</a></div></div> ';
# Просмотры
$post->bottom .= '<div class="item"><div class="content"><a href="news.views.php?id=' . $news['id'] . '" class="header" data-tooltip="' . __('Просмотров %s', $views) . '" data-position="top center"><i class="fa fa-eye fa-fw"></i> ' . __('%s', $views) . '</a></div></div> ';
# Мне нравится
$stt = $db->query("SELECT * FROM `news_like` WHERE `id_user` = '$user->id' AND `id_news` = '" . intval($news['id']) . "' LIMIT 1")->fetch();

if ($user->id && $user->id != $ank->id && !$stt) {
    $post->bottom .= '<div class="item"><div class="content"><a href="comments.php?id=' . $news['id'] . '&amp;likes" data-tooltip="' . __('Мне нравится') . '" data-position="top center" class="header"><i class="fa fa-heart-o fa-fw"></i> ' . __('%s', $like) . '</a></div></div>';
} elseif ($user->id && $user->id != $ank->id) {
    $post->bottom .= '<div class="item"><div class="content"><a href="news.like.php?id=' . $news['id'] . '" class="header" data-tooltip="' . __('Вам понравилось') . '" data-position="top center"><span style="color: #e81c4f"><i class="fa fa-heart fa-fw"></i> ' . __('%s', $like) . '</span></a></div></div>';
} else {
    $post->bottom .= '<div class="item"><div class="content"><a href="news.like.php?id=' . $news['id'] . '" class="header" data-tooltip="' . __('Оценили %s', $like) . '" data-position="top center"><i class="fa fa-heart fa-fw"></i> ' . __('%s', $like) . '</a></div></div>';
}
$post->bottom .= '<div class="item"><div class="content"><a href="/profile.view.php?id=' . $news['id_user'] . '" class="header" data-tooltip="' . __('Автор') . '" data-position="top center">' . $ank->nick() . '</a></div></a></div>';
$post->bottom .= '</div>';


$listing->display();

# Вывод опросов
include 'news.votes.php';

$ank = new user($news['id_user']);

$pages = new pages($db->query("SELECT COUNT(*) FROM `news_comments` WHERE `id_news` = '" . $news['id'] . "'")->fetchColumn());

$can_write = true;
/** @var $user \user */
if (!$user->is_writeable) {
    $doc->msg(__('Писать запрещено'), 'write_denied');
    $can_write = false;
}

if ($can_write && $pages->this_page == 1) {

    if (isset($_POST['send']) && isset($_POST['message']) && isset($_POST['token']) && $user->group) {
        $message = (string) $_POST['message'];
        $users_in_message = text::nickSearch($message);
        $message = text::input_text($message);

        if (!antiflood::useToken($_POST['token'], 'news')) {
            // нет токена (обычно, повторная отправка формы)
        } elseif ($dcms->censure && $mat = is_valid::mat($message)) {
            $doc->err(__('Обнаружен мат: %s', $mat));
        } elseif ($message) {
            $user->balls++;

            $res = $db->prepare("INSERT INTO `news_comments` (`id_news`, `id_user`, `time`, `text`) VALUES (?,?,?,?)");
            $res->execute(Array($news['id'], $user->id, TIME, $message));
            header('Refresh: 1; url=?id=' . $id . '&' . passgen());

            $doc->ret(__('Вернуться'), '?id=' . $id . '&amp;' . passgen());
            $doc->msg(__('Комментарий успешно оставлен'));

            $id_message = $db->lastInsertId();

            if ($users_in_message) {
                for ($i = 0; $i < count($users_in_message) && $i < 20; $i++) {
                    $user_id_in_message = $users_in_message[$i];
                    if ($user_id_in_message == $user->id) {
                        continue;
                    }
                    $ank_in_message = new user($user_id_in_message);
                    if ($ank_in_message->notice_mention) {
                        $ank_in_message->not(($user->sex ? 'упомянул' : 'упомянула') . " о вас в комментарии к новости [url=/news/comments.php?id={$news['id']}#comment{$id_message}]$news[title][/url]", $user->id);
                    }
                }
            }

            if ($doc instanceof document_json) {
                $doc->form_value('message', '');
                $doc->form_value('token', antiflood::getToken('news'));
            }

            exit;
        } else {
            $doc->err(__('Сообщение пусто'));
        }

        if ($doc instanceof document_json && $user->group) {
            $doc->form_value('token', antiflood::getToken('news'));
        }
    }

    if ($user->group) {
        $message_form = '';
        if (isset($_GET['com']) && is_numeric($_GET['com'])) {
            $id_message = (int) $_GET['com'];
            $q = $db->prepare("SELECT * FROM `news_comments` WHERE `id` = ? LIMIT 1");
            $q->execute(Array($id_message));
            if ($messag = $q->fetch()) {
                $ank = new user($messag['id_user']);
                if (isset($_GET['reply'])) {
                    $message_form = '@' . $ank->login . ', ';
                } elseif (isset($_GET['quote'])) {
                    $message_form = "[quote id_user=\"{$ank->id}\" time=\"{$messag['time']}\"]{$messag['text']}[/quote] ";
                }
            }
        }

        if (!AJAX) {
            $form = new form('?id=' . $id . '&' . passgen());
            $form->refresh_url('?id=' . $id . '&' . passgen());
            $form->setAjaxUrl('?id=' . $id . '&');
            $form->hidden('token', antiflood::getToken('news'));
            $form->textarea('message', __('Сообщение'), $message_form, true);
            $form->button(__('Отправить'), 'send', false);
            $form->display();
        }
    }
}

$listing = new ui_components();
$listing->ui_comment = true; //подключаем css comments
$listing->ui_segment = true; //подключаем css segment
$listing->class = $dcms->browser_type == 'full' ? 'segments minimal comments' : 'segments comments';


if (!empty($form)) {
    $listing->setForm($form);
}

$q = $db->prepare("SELECT * FROM `news_comments` WHERE `id_news` = ? ORDER BY `id` DESC LIMIT $pages->limit");
$q->execute(Array($news['id']));
$after_id = false;

if ($arr = $q->fetchAll()) {
    foreach ($arr AS $message) {
        $ank = new user($message['id_user']);
        $post = $listing->post();
        $post->class = 'ui segment comment';
        $post->comments = true;
        $post->id = 'news_' . $message['id'];
        $post->url = "actions.php?id=$news[id]&amp;comment=" . $message['id'];
        $post->time = misc::times($message['time']);
        $post->login = $ank->nick();
        $post->avatar = $ank->getAvatar();
        $post->image_a_class = 'ui avatar';
        $post->content = text::toOutput($message['text']);

        if ($user->group && ($user->id != $ank->id)) {
            $post->action(false, "?id=$news[id]&amp;com=$message[id]&amp;reply", __('Ответить'));
        }
        if ($user->group) {
            $post->action(false, "?id=$news[id]&amp;com=$message[id]&amp;quote", __('Цитировать'));
        }
        if ($user->group >= 2) {
            $post->action(false, "comment.delete.php?id=$message[id]&amp;return=" . URL, __('Удалить'));
        }

        if (!$doc->last_modified) {
            $doc->last_modified = $message['time'];
        }

        if ($doc instanceof document_json) {
            $doc->add_post($post, $after_id);
        }

        $after_id = $post->id;
    }
}

if ($doc instanceof document_json && !$arr) {
    $post = new ui_compost(__('Комментарии отсутствуют'));
    $post->icon('clone');
    $doc->add_post($post);
}

$listing->setAjaxUrl('?id=' . $id . '&amp;page=' . $pages->this_page);
$listing->display(__('Комментарии отсутствуют'));
$pages->display('?id=' . $id . '&amp;'); // вывод страниц

if ($doc instanceof document_json) {
    $doc->set_pages($pages);
}