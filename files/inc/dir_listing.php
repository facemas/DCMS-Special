<?php

defined('SOCCMS') or die();
$dir = new files($abs_path);

if ($dir->group_show > $user->group) {
    $doc->access_denied(__('У Вас нет прав для просмотра данной папки'));
}

$access_write = $dir->group_write <= $user->group || ($dir->id_user && $user->id == $dir->id_user);
$access_edit = $dir->group_edit <= $user->group;

$doc->title = $dir->runame;
$doc->description = $dir->meta_description;
$doc->keywords = $dir->meta_keywords ? explode(',', $dir->meta_keywords) : '';

if ($access_write || $access_edit) {
    include H . '/files/inc/dir_act.php';
}

$order_keys = $dir->getKeys();
if (!empty($_GET ['order']) && isset($order_keys[$_GET['order']])) {
    $order = $_GET ['order'];
} else {
    $order = $dir->sort_default;
}

if ($screens = $dir->getScreens()) {
    
}

$search = false;
if (!empty($_GET ['search'])) {
    $search = text::input_text($_GET ['search']);
}

if (empty($_GET ['act'])) {
    $form = new form("?", false);
    $form->hidden('order', $order);
    $form->block('<div class="ui action input">');
    $form->block('<input type="text" name="search" placeholder="' . __('Имя файла (или его часть)') . '" value="' . $search . '">');
    $form->button(null, false, false, 'ui fa button', 'fa fa-search');
    $form->block('</div>');
    $form->display();
}

if ($search) {
    $doc->msg(__('Результаты поиска по запросу: %s', $search));
}

$content = $dir->getList($order, $search);

$dirs = & $content ['dirs'];
$files = & $content ['files'];


if ($description = $dir->description) {
    $listing = new listing();
    $post = $listing->post();
    $post->title = __('Информация');
    $post->icon('info');
    $post->content[] = $description;
    $post->highlight = true;
    $listing->display();
}

$listing = new listing();

$pages = new pages();
$pages->posts = count($files);
// меню сортировки
$ord = array();
$order_keys = $dir->getKeys();
foreach ($order_keys as $key => $name) {
    $ord [] = array("?order=$key&amp;page={$pages->this_page}" . (!empty($search) ? '&amp;search=' . urlencode($search) : ''), $name, $order == $key);
}

$or = new design();
$or->assign('order', $ord);
if (empty($_GET['act']) && $pages->posts) {
    $or->display('design.order.tpl');
}

if ($pages->this_page == 1) {
    // показываем все папки (без листинга) только на первой странице
    $c_dirs = count($dirs);
    for ($i = 0; $i < $c_dirs; $i++) {
        $post = $listing->post();

        $description = '';
        if ($dirs [$i]->group_show) {
            $description = '[b]' . __('Доступ только группе %s и выше', groups::name($dirs [$i]->group_show)) . "[/b]\n";
        }
        $description .= $dirs [$i]->description;


        $post->title = text::toValue($dirs [$i]->runame);

        $count_new = $dirs [$i]->count(true);
        if ($count_new) {
            $post->counter = '+' . $count_new;
            $post->url = '/files' . $dirs [$i]->getPath() . '?order=time_add:desc';
            $post->highlight = true;
        } else {
            $post->url = '/files' . $dirs [$i]->getPath();
        }

        $post->post = text::toOutput($description);
        $post->icon($dirs[$i]->icon());
    }
}

$start = $pages->my_start();
$end = $pages->end();

$show_key = strtok($order, ':');
$time_new = mktime(-24);
for ($i = $start; $i < $end && $i < $pages->posts; $i++) {
    switch ($show_key) {
        case 'comments' :
            $post2 = __('Комментариев') . ': ' . intval($files [$i]->comments) . "\n";
            break;
        case 'title' :
            $post2 = __('Заголовок') . ': ' . text::toValue($files [$i]->title) . "\n";
            break;
        case 'track_number' :
            $post2 = __('Номер трека') . ': ' . text::toValue($files [$i]->track_number) . "\n";
            break;
        case 'genre' :
            $post2 = __('Жанр') . ': ' . text::toValue($files [$i]->genre) . "\n";
            break;
        case 'album' :
            $post2 = __('Альбом') . ': ' . text::toValue($files [$i]->album) . "\n";
            break;
        case 'band' :
            $post2 = __('Группа') . ': ' . text::toValue($files [$i]->band) . "\n";
            break;
        case 'artist' :
            $post2 = __('Исполнители') . ': ' . text::toValue($files [$i]->artist) . "\n";
            break;
        case 'size' :
            $post2 = __('Размер') . ': ' . misc::getDataCapacity($files [$i]->size) . "\n";
            break;
        case 'rating' :
            $post2 = __("Общая оценка") . ': ' . ' ' . $files [$i]->rating_name . ' (' . round($files [$i]->rating, 1) . '/' . $files [$i]->rating_count . ")\n";
            break;
        case 'time_create' :
            $post2 = __('Файл создан') . ': ' . misc::when($files [$i]->time_create) . "\n";
            break;
        case 'downloads' :
            $post2 = __('Файл скачан') . ': ' . intval($files [$i]->downloads) . ' ' . __(misc::number($files [$i]->downloads, 'раз', 'раза', 'раз')) . "\n";
            break;
        case 'id_user' :
            $ank = new user($files [$i]->id_user);
            $post2 = __('Добавил' . ($ank->sex ? '' : 'а')) . ': ' . $ank->login . "\n";
            break;
        default :
            $post2 = '';
            break;
    }

    if ($properties = $files [$i]->properties) {
        // Параметры файла (только основное)
        $post2 .= $properties . "\n";
    }

    if ($description = $files [$i]->description_small) {
        // краткое описание
        $post2 .= $description . "\n";
    }

    $post = $listing->post();
    $post->title = text::toValue($files [$i]->runame);
    $post->post = text::toOutput($post2);
    $post->highlight = $files [$i]->time_add > $time_new;
    $post->url = "/files" . $files [$i]->getPath() . ".htm?order=$order";
    $post->icon($files [$i]->icon());
    $post->image = $files [$i]->image();
    $post->time = misc::times($files [$i]->time_add);
}


if (empty($_GET ['act'])) {
    $listing->display(__('Папка пуста'));
    $pages->display('?order=' . $order . '&amp;' . (!empty($search) ? 'search=' . urlencode($search) . '&amp;' : '')); // вывод страниц
} else {
    $doc->act(text::toValue($dir->runame), '?' . passgen());
}

$return = $dir->ret(5); // последние 5 ссылок пути


for ($i = 0; $i < count($return); $i++) {
    $doc->act($return [$i] ['runame'], '/files' . $return [$i] ['path']);
}
if ($access_write || $access_edit) {
    include H . '/files/inc/dir_form.php';
}
exit;
