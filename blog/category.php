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

if ($user->id) {
    $doc->opt(__('Добавить запись'), 'new.blog.php?id_cat=' . $topic['id'] . "&amp;return=" . URL, false, '<i class="fa fa-plus fa-fw"></i>');
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

$listing = new listing();
while ($blog = $q->fetch()) {
    $post = $listing->post();
    $ank = new user((int) $blog['autor']);
    if ($blog['block'] == 1) {
        $post->icon('book');
    } else {
        $post->icon('book');
    }
    $post->title = text::toValue($blog['name']);
    $post->url = 'blog.php?blog=' . $blog['id'];
    $post->time = misc::times($blog['time_create']);
    $post->post .= "<i class='fa fa-comments-o fa-fw'></i> " . __('%s', $blog['comm']) . " ";
    $post->post .= "<i class='fa fa-eye fa-fw'></i> " . __('%s', $blog['view']) . "<br />";
}
$listing->display(__('Нет результатов'));
$pages->display('category.php?id=' . $topic['id'] . '&amp;'); // вывод страниц
if ($topic['group_edit'] <= $user->group) {
    $doc->opt(__('Параметры раздела'), 'category.edit.php?id=' . $topic['id'] . "&amp;return=" . URL, false, '<i class="fa fa-cog fa-fw"></i>');
}
$doc->ret(__('Блоги'), './');
