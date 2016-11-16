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
$listing = new listing();
while ($blog = $q->fetch()) {
    $post = $listing->post();
    $ank = new user((int) $blog['autor']);
    $post->title = text::toValue($blog['name']);
    $post->icon('book');
    $post->time = misc::when($blog['time_create']);
    $post->bottom = "<i class='fa fa-comment-o fa-fw'></i> $blog[comm] <i class='fa fa-eye fa-fw'></i> $blog[view]";
    $post->url = 'blog.php?blog=' . $blog['id'];
    $post->content = text::toOutput($blog['message']);
    $post->time = misc::times($blog['time_create']);
}
$listing->display(__('Записи отсутствуют'));
$pages->display('?id=' . $ank->id . '&amp;sort=' . $sort . ''); // вывод страниц
$doc->ret(__('Блоги'), 'index.php');
?>