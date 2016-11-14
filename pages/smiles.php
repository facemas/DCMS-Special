<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Смайлы');
$smiles = smiles::get_ini();
$smiles_a = array();
// загружаем список смайлов
$smiles_gl = (array) glob(H . '/sys/images/smiles/*.gif');

foreach ($smiles_gl as $path) {
    if (preg_match('#/([^/]+)\.gif$#', $path, $m))
        $smiles_a[] = $m[1];
}

$pages = new pages ();
$pages->posts = count($smiles_a);
//$pages->this_page();
$start = $pages->my_start();
$end = $pages->end();

$listing = new listing();
for ($i = $start; $i < $end && $i < $pages->posts; $i++) {
    $post = $listing->post();
    $post->title = text::toValue($smiles_a[$i]);
    $post->image = '/sys/images/smiles/' . $smiles_a[$i] . '.gif';
    $post->content = __('Варианты') . ': *' . implode('*, *', array_keys($smiles, $smiles_a[$i])) . '*';
}
$listing->display(__('Нет результатов'));
$pages->display('?'); // вывод страниц

if (!empty($_GET['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
}
