<?php

include_once '../sys/inc/start.php';

$doc = new document();
$doc->title = __('Новые файлы');

// абсолютный путь к папке загруз-центра с учетом выбора пользователя
$abs_path = realpath(FILES . '/' . @$_GET['dir']);

// если в строку $abs_path не входит FILES, то это попытка залезть на уровень выше дозволеного
if (strpos($abs_path, FILES) !== 0 || !file_exists($abs_path)) {
    header('Location: ../?' . SID);
    exit;
}

$dir = new files($abs_path);

if ($dir->group_show > $user->group) {
    $doc->access_denied(__('У Вас нет прав для просмотра новых файлов в данной папке'));
}

$doc->title = __('%s - Новые файлы', $dir->runame);

$content = $dir->getNewFiles();

$files = & $content['files'];

$listing = new listing();
$pages = new pages;
$pages->posts = count($files);

$start = $pages->my_start();
$end = $pages->end();
for ($i = $start; $i < $end && $i < $pages->posts; $i++) {
    $ank = new user($files[$i]->id_user);

    $post = $listing->post();
    $post->title = text::toValue($files[$i]->runame);
    $post->url = "/files" . $files[$i]->getPath() . ".htm";
    $post->image = $files[$i]->image();
    $post->icon($files[$i]->icon());
    $post1 = __('Файл добавлен') . ': ' . misc::when($files[$i]->time_add) . ($ank->id ? ' (' . $ank->nick . ')' : '') . "<br />\n";


    $post2 = '';
    if ($properties = $files[$i]->properties) { // Параметры файла (только основное)
        $post2 .= $properties . "\n";
    }

    if ($description = $files[$i]->description_small) {
        $post2 .= $description . "\n";
    }

    $post->post = $post1 . text::toOutput($post2);
}

$listing->display(__('За последние сутки небыло добавлено ни одного файла'));
$pages->display('?dir=' . $dir->path_rel . '&amp;'); // вывод страниц