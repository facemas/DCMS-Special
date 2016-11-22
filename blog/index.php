<?php

include_once '../sys/inc/start.php';

$doc = new document();
$doc->title = __('Блоги');

if ($user->group > 0) {
    $listing = new ui_components();
    $listing->ui_menu = true;

    $post = $listing->post();
    $post->head = '
        <div class="ui icon menu">
            <span data-tooltip="' . __('Мои записи') . '" data-position="bottom left">
                <a class="item" href="my.php"><i class="fa fa-pencil-square fa-fw"></i></a>
            </span>
            
            ' . ($user->group >= 4 ? '
                <span data-tooltip="' . __('Параметры блогов') . '" data-position="bottom left">
                    <a class="item" href="blog.settings.php"><i class="fa fa-cog fa-fw"></i></a>
                </span>
                
                <span data-tooltip="' . __('Создать категорию') . '" data-position="bottom left">
                    <a class="item" href="category.create.php"><i class="fa fa-plus fa-fw"></i> ' . __('Создать категорию') . '</a>
                </span>
            ' : null) . '
        </div>';

    $listing->display();
}

$res = $db->prepare("SELECT COUNT(*) FROM `blog` WHERE `id` <= ?");
$res->execute(Array($user->group));
$blog = $res->fetchColumn();

if ($blog > 0) {
# Выводим последние записи блогов
    $listing = new ui_components();
    $listing->ui_comment = true; //подключаем css comment
    $listing->ui_segment = true; //подключаем css segment
    $listing->ui_list = true; //подключаем css list
    $listing->class = $dcms->browser_type == 'full' ? 'segments minimal comments' : 'segments comments';

    $post = $listing->post();
    $post->class = 'ui secondary segment';
    $post->title = __('Последние записи');
    $post->icon('book');

    $q = $db->query("SELECT * FROM `blog` ORDER BY `id` DESC LIMIT 5");

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
}

# Выводим категории блогов

$listing = new ui_components();
$listing->ui_segment = true; //подключаем css segments
$listing->class = 'ui segments';

$res = $db->prepare("SELECT COUNT(*) FROM `blog_cat` WHERE `group_show` <= ?");
$res->execute(Array($user->group));
$blog_cat = $res->fetchColumn();

if ($blog_cat > 0) {
    $post = $listing->post();
    $post->class = 'ui secondary segment';
    $post->title = __('Категории');
    $post->icon('th-list');
}

$res = $db->prepare("SELECT COUNT(*) FROM `blog_cat` WHERE `group_show` <= ?");
$res->execute(Array($user->group));
$pages = new pages();
$pages->posts = $res->fetchColumn();

$q = $db->prepare("SELECT * FROM `blog_cat` WHERE `group_show` <= ? ORDER BY `position` ASC LIMIT $pages->limit");
$q->execute(Array($user->group));

while ($category = $q->fetch()) {
    # Проверяем иконку категории на существование
    $iconBlog = ($category['icon'] ? $category['icon'] : 'folder-open');
    # Счетчик блогов в категории
    $res = $db->query("SELECT COUNT(*) FROM `blog` WHERE `id_cat` = '$category[id]'");
    $blogCount = $res->fetchColumn();

    $post = $listing->post();
    $post->class = 'ui segment';
    $post->ui_label = true;
    $post->list = true;
    $post->url = "category.php?id=$category[id]";
    $post->title = text::toOutput($category['name']);
    $post->icon($iconBlog);
    $post->counter = $blogCount;

    if ($category['description']) {
        $post->post = "<span style='color: grey'>" . text::toOutput($category['description']) . "</span>";
    }
}
$listing->display(__('Доступных Вам категорий нет'));
$pages->display('?');

