<?php

include_once '../sys/inc/start.php';
$doc = new document(groups::max());
$doc->title = __('Список изменений');

$files = array();
$files_g = (array) glob(H . '/sys/docs/changelog/*.txt');
foreach ($files_g as $path) {
    if (preg_match("#([^/]*?)\.txt#", $path, $m)) {
        $files[] = $m[1];
    }
}

$files = array_reverse($files);

if (!empty($_GET['ver'])) {
    if (!in_array($_GET['ver'], $files)) {
        $doc->err(__('Список изменения к данной версии не найден'));
    } else {
        $fname = basename($_GET['ver']);
        $bb = new bb(H . '/sys/docs/changelog/' . $fname . '.txt');
        if ($bb->title) {
            $doc->title = $bb->title;
        }
        $bb->display();
        $doc->ret(__('Список версий'), '?');
        $doc->ret(__('Управление'), './');
        exit;
    }
}

$listing = new listing();
foreach ($files AS $name) {
    $post = $listing->post();
    $post->title = text::toValue($name);
    $post->url = '?ver=' . urlencode($name);
    $post->icon('bug');
}
$listing->display(__('Списки изменений отсутствуют'));

$doc->ret(__('Управление'), './');
