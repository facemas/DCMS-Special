<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Блоги');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора раздела'));
    exit;
}

$id_top = (int) $_GET['id'];
$q = $db->prepare("SELECT * FROM `blog_cat` WHERE `id` = ?");
$q->execute(Array($id_top));

if (!$topic = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Категория не доступна'));
    exit;
}

$doc->title .= ' - ' . $topic['name'];

$res = $db->prepare("SELECT COUNT(*) FROM `blog` WHERE `id_cat` = ?");
$res->execute(Array($topic['id']));

$pages = new pages;
$pages->posts = $res->fetchColumn(); // количество категорий 
$pages->this_page(); // получаем текущую страницу

if ($user->group > 0) {
    $listing = new ui_components();
    $listing->ui_menu = true;

    $post = $listing->post();
    $post->head = '
        <div class="ui icon menu">
            ' . ($topic['group_edit'] <= $user->group ? '
            <span data-tooltip="' . __('Параметры раздела') . '" data-position="bottom left">
                <a class="item" href="category.edit.php?id=' . $topic['id'] . '&amp;return=' . URL . '"><i class="fa fa-cog fa-fw"></i></a>
            </span>
            ' : null) . '
            
            <span data-tooltip="' . __('Добавить запись') . '" data-position="bottom left">
                <a class="item active" href="blog.create.php?id_cat=' . $topic['id'] . '&amp;return=' . URL . '"><i class="fa fa-plus-square fa-fw"></i> ' . __('Добавить запись') . '</a>
            </span>
        </div>';

    $listing->display();
}

$sort = (isset($_GET['sort'])) ? htmlspecialchars($_GET['sort']) : null;
switch ($sort) {
    case 'view':
        $order = 'view';
        $sort = 'DESC';
        break;
    case 'id':
        $order = 'id';
        $sort = 'DESC';
        break;
    case 'comm':
        $order = 'comm';
        $sort = 'DESC';
        break;
    default:
        $order = 'id';
        $sort = 'DESC';
        break;
}
$ord = array();
$ord[] = array("?id=" . $topic['id'] . "&amp;sort=id&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('Новые'), $order == 'id');
$ord[] = array("?id=" . $topic['id'] . "&amp;sort=view&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('По кол-ву просмотров'), $order == 'view');
$ord[] = array("?id=" . $topic['id'] . "&amp;sort=comm&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('Обсуждаемые'), $order == 'comm');
$or = new design();
$or->assign('order', $ord);
$or->display('design.order.tpl');

$q = $db->prepare("SELECT * FROM `blog` WHERE `id_cat` = ?  ORDER BY `$order` " . $sort . "  LIMIT " . $pages->limit);
$q->execute(Array($topic['id']));

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
$pages->display('category.php?id=' . $topic['id'] . '&amp;'); // вывод страниц

$doc->ret(__('Блоги'), './');
