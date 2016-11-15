<?php

include_once '../sys/inc/start.php';
$doc = new document();

$doc->title = __('Новости');

$pages = new pages;
$res = $db->query("SELECT COUNT(*) FROM `news`");
$pages->posts = $res->fetchColumn();

$res = $db->query("SELECT COUNT(*) FROM `news`");
$newsCount = $res->fetchColumn();

$q = $db->query("SELECT * FROM `news` ORDER BY `id` DESC LIMIT " . $pages->limit);

$listing = new ui_components();
$listing->ui_comment = true; //подключаем css comments
$listing->ui_segment = true; //подключаем css segment
$listing->ui_list = true; //подключаем css segment
$listing->class = $dcms->browser_type == 'full' ? 'segments minimal comments' : 'segments comments';

$post = $listing->post();
$post->head = "<h5 class='ui secondary segment'><i class='fa fa-feed fa-fw'></i> " . __('Все новости') . " <span style='float: right'>$newsCount</span></h5>";

if ($arr = $q->fetchAll()) {
    foreach ($arr AS $news) {
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
            $post->bottom .= '<div class="item"><div class="content"><a href="news.like.php?id=' . $news['id'] . '" class="header" data-tooltip="' . __('Посмотреть кому понравилось') . '" data-position="top center"><i class="fa fa-heart fa-fw"></i> ' . __('%s', $like) . '</a></div></div>';
        }
        $post->bottom .= '<div class="item"><div class="content"><a href="/profile.view.php?id=' . $news['id_user'] . '" class="header" data-tooltip="' . __('Автор') . '" data-position="top center">' . $ank->nick() . '</a></div></a></div>';
        $post->bottom .= '</div>';
    }
}

$listing->display(__('Новости отсутствуют'));

$pages->display('?'); // вывод страниц

if ($user->group >= 4) {
    $doc->opt(__('Добавить новость'), 'news.add.php', false, '<i class="fa fa-plus fa-fw"></i>');
}