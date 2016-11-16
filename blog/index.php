<?php

include_once '../sys/inc/start.php';

$doc = new document();
$doc->title = __('Блоги');

# Выводим последние записи блогов
$listing = new listing();

$post = $listing->post();
$post->title = __('Последние записи');
$post->highlight = true;
$post->icon('book');

$q = $db->query("SELECT * FROM `blog` ORDER BY `id` DESC LIMIT 5");

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

# Выводим категории блогов

$listing = new listing();
$post = $listing->post();
$post->hightlight = true;
$post->title = __('Категории');
$post->icon('th-list');
if ($user->group >= 4) {
    $post->counter = "<a href='category.create.php' class='hint--left' aria-label='".__('Создать категорию')."'><i class='fa fa-plus'></i></a>";
}

$pages = new pages($db->query("SELECT COUNT(*) FROM `blog_cat`")->fetchColumn());
$pages->this_page(); // получаем текущую страницу

$q = $db->prepare("SELECT * FROM `blog_cat` WHERE `group_show` <= ? ORDER BY `position` ASC LIMIT $pages->limit");
$q->execute(Array($user->group));

while ($category = $q->fetch()) {
    # Проверяем иконку категории на существование
    $iconBlog = ($category['icon'] ? $category['icon'] : 'folder-open');
    # Счетчик блогов в категории
    $res = $db->query("SELECT COUNT(*) FROM `blog` WHERE `id_cat` = '$category[id]'");
    $blogCount = $res->fetchColumn();

    $post = $listing->post();
    $post->url = "category.php?id=$category[id]";
    $post->title = text::toOutput($category['name']);
    $post->icon($iconBlog);
    $post->counter = $blogCount;

    if ($category['description']) {
        $post->post = "<span style='color: grey'>" . text::toOutput($category['description']) . "</span>";
    }
}
$listing->display(__('Доступных Вам категорий нет'));
$pages->display('?'); // вывод страниц

if ($user->group >= 4) {
    $doc->opt(__('Параметры'), 'sys.blog.php', false, '<i class="fa fa-cogs fa-fw"></i>');
    //$doc->opt(__('Создать категорию'), 'category.create.php', false, '<i class="fa fa-plus fa-fw"></i>');
}
if ($user->group > 0) {
    $doc->opt(__('Мои записи'), 'my.php', false, '<i class="fa fa-pencil-square fa-fw"></i>');
}
