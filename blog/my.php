<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Блоги');

if (isset($_GET ['id'])) {
    $ank = new user((int) $_GET ['id']);
} else {
    $ank = $user;
}
if (!$ank->group) {
    $doc->access_denied(__('Ошибка выбора'));
}
if ($user->id && $ank->id == $user->id) {
    $doc->title = __('Мои записи');
} else {
    $doc->title = __('Записи "%s"', $ank->login);
}

switch (@$_GET['sort']) {
    case 'view':
        $order = 'view';
        $sort = 'DESC';
        $doc->title = __('По кол-ву просмотров');
        break;
    case 'id':$order = 'id';
        $sort = 'DESC';
        $doc->title = __('Новые');
        break;
    case 'comm':
        $order = 'comm';
        $doc->title = __('Самые обсуждаемые');
        $sort = 'DESC';
        break;
    default:
        $order = 'id';
        $sort = 'DESC';
        break;
}
$res = $db->prepare("SELECT COUNT(*) FROM `blog` WHERE `autor` = ?");
$res->execute(Array($ank->id));

$pages = new pages;
$pages->posts = $res->fetchColumn(); // количество категорий форума
$pages->this_page(); // получаем текущую страницу

$ord = array();
$ord[] = array("?sort=id&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('Новые'), $order == 'id');
$ord[] = array("?sort=view&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('По кол-ву просмотров'), $order == 'view');
$ord[] = array("?sort=comm&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('Самые обсуждаемые'), $order == 'comm');
$or = new design();
$or->assign('order', $ord);
$or->display('design.order.tpl');

$q = $db->prepare("SELECT * FROM `blog` WHERE `autor` = ?  ORDER BY `$order` " . $sort . "  LIMIT " . $pages->limit);
$q->execute(Array($ank->id));

$listing = new ui_components();
$listing->ui_comment = true; //подключаем css comments
$listing->ui_segment = true; //подключаем css segment
$listing->ui_list = true; //подключаем css list
$listing->class = $dcms->browser_type == 'full' ? 'segments minimal comments' : 'segments comments';

while ($blog = $q->fetch()) {
    $ank = new user((int) $blog['autor']);

    $post = $listing->post();
    $post->class = 'ui segment comment';
    $post->comments = true;

    if ($blog['block'] == 1) {
        $post->icon('book');
    } else {
        $post->icon('book');
    }
    $post->title = text::toValue($blog['name']);
    $post->url = 'blog.php?blog=' . $blog['id'];
    $post->time = misc::times($blog['time_create']);
    $post->content = text::toOutput($blog['message']);
    $post->bottom .= '<div class="ui very relaxed horizontal list"> ';

    # Счетчик лайков
    $res = $db->prepare("SELECT COUNT(*) FROM `blog_like` WHERE `id_blog` = ?");
    $res->execute(Array(intval($blog['id'])));
    $like = $res->fetchColumn();

    # Счетчик просмотров
    $res = $db->prepare("SELECT COUNT(*) FROM `blog_views` WHERE `id_blog` = ?");
    $res->execute(Array(intval($blog['id'])));
    $views = $res->fetchColumn();

    # Комментарии
    $post->bottom .= '<div class="item"><div class="content"><a href="blog.php?blog=' . $blog['id'] . '" class="header" data-tooltip="' . __('Комментариев %s', $blog['comm']) . '" data-position="top left"><i class="fa fa-comments fa-fw"></i> ' . $blog['comm'] . '</a></div></div> ';
    # Просмотры
    $post->bottom .= '<div class="item"><div class="content"><a href="blog.views.php?id=' . $blog['id'] . '" class="header" data-tooltip="' . __('Просмотров %s', $views) . '" data-position="top center"><i class="fa fa-eye fa-fw"></i> ' . $views . '</a></div></div> ';

    $post->bottom .= '<div class="item"><div class="content"><a href="blog.like.php?id=' . $blog['id'] . '" class="header" data-tooltip="' . __('Оценили %s', $like) . '" data-position="top center"><i class="fa fa-heart fa-fw"></i> ' . $like . '</a></div></div>';

    $post->bottom .= '<div class="item"><div class="content"><a href="/profile.view.php?id=' . $blog['autor'] . '" class="header" data-tooltip="' . __('Автор') . '" data-position="top center">' . $ank->nick() . '</a></div></a></div>';
    $post->bottom .= '</div>';
}
$listing->display(__('Нет результатов'));

$pages->display('?id=' . $ank->id . '&amp;sort=' . $sort . ''); // вывод страниц
$doc->ret(__('Блоги'), 'index.php');
?>