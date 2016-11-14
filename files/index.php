<?php

include_once '../sys/inc/start.php';

if (AJAX) {
    $doc = new document_json();
} else {
    $doc = new document();
}

$doc->title = __('Файлы');

$path = @$_GET['path'];
if (preg_match('#(.+)\.htm$#', $path, $m)) {
    $path = $m[1];
    $file_description = true;
}
// абсолютный путь к папке загруз-центра с учетом выбора пользователя
$abs_path = realpath(FILES . '/' . $path);
// если в строку $abs_path не входит FILES, то это попытка залезть на уровень выше дозволеного,
// поэтому $abs_path будет корнем загруз-центра
if (strpos($abs_path, FILES) !== 0 || !file_exists($abs_path)) {
    header('Location: ../?' . SID);
    exit;
} //$abs_path = FILES;
$rel_path = str_replace(FILES, '', $abs_path); // получаем относительный путь
// файл (описание)
if (!empty($file_description) && is_file($abs_path)) {
    include 'inc/file_description.php';
}

// файл (скачивание)
elseif (is_file($abs_path)) {
    include 'inc/file_download.php';
}

// папка
elseif (is_dir($abs_path)) {
    include 'inc/dir_listing.php';
}