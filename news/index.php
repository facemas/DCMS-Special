<?php

include_once '../sys/inc/start.php';
$doc = new document();

$doc->title = __('Новости');

$pages = new pages;
$res = $db->query("SELECT COUNT(*) FROM `news`");
$pages->posts = $res->fetchColumn(); // количество сообщений

$q = $db->query("SELECT * FROM `news` ORDER BY `id` DESC LIMIT " . $pages->limit);

$listing = new listing();
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $news) {
        $post = $listing->post();
        $ank = new user((int) $news['id_user']);

        $post->icon('feed');
        $post->content = text::toOutput($news['text']);
        $post->title = text::toValue($news['title']);
        $post->url = 'comments.php?id=' . $news['id'];
        $post->time = misc::times($news['time']);

        $post = $listing->post();

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

        # Комментарии
        $post->title .= ' <a href="comments.php?id=' . $news['id'] . '" class="btn btn-secondary btn-sm"><i class="fa fa-comments-o fa-fw"></i> ' . __('%s', $comments) . '</a> ';
        # Просмотры
        $post->title .= ' <a href="news.views.php?id=' . $news['id'] . '" class="btn btn-secondary btn-sm"><i class="fa fa-eye fa-fw"></i> ' . __('%s', $views) . '</a> ';
        # Мне нравится
        $stt = $db->query("SELECT * FROM `news_like` WHERE `id_user` = '$user->id' AND `id_news` = '" . intval($news['id']) . "' LIMIT 1")->fetch();

        if ($user->id && $user->id != $ank->id && !$stt) {
            $post->title .= '<a href="?id=' . $news['id'] . '&amp;like" class="btn btn-secondary btn-sm">' . __('Мне нравится') . '</a> <a href="news.like.php?id=' . $news['id'] . '" class="btn btn-secondary btn-sm"><i class="fa fa-thumbs-o-up fa-fw"></i> ' . __('%s', $like) . '</a>';
        } elseif ($user->id && $user->id != $ank->id) {
            $post->title .= '<a href="news.like.php?id=' . $news['id'] . '" class="btn btn-secondary btn-sm"><i class="fa fa-thumbs-o-up fa-fw"></i> ' . __('%s', $like) . '</a>';
        } else {
            $post->title .= '<a href="news.like.php?id=' . $news['id'] . '" class="btn btn-secondary btn-sm"><i class="fa fa-thumbs-o-up fa-fw"></i> ' . __('%s', $like) . '</a>';
        }
        $post->title .= ' <a href="/profile.view.php?id=' . $news['id_user'] . '" class="btn btn-secondary btn-sm" style="float: right;">' . $ank->nick() . '</a>';
    }
}

$listing->display(__('Новости отсутствуют'));

$pages->display('?'); // вывод страниц

if ($user->group >= 4) {
    $doc->opt(__('Добавить новость'), 'news.add.php', false, '<i class="fa fa-plus fa-fw"></i>');
}