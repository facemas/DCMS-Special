<?php

include_once '../sys/inc/start.php';
$doc = new document(5);

$doc->title = __('Настройки записей');

$q = $db->prepare("SELECT * FROM `blog_cfg` WHERE `id` = ?");
$q->execute(Array(1));
$blog = $q->fetch();

if (isset($_POST ['maxfile'])) {
    $filemax = (int) ($_POST['maxfile'] * 1024);
    if ($filemax != $blog ['file']) {
        $q = $db->prepare("UPDATE `blog_cfg` SET `file` = ? WHERE `id` = ? LIMIT 1");
        $q->execute(Array($filemax, 1));
        $doc->msg(__('Размер файла ' . $filemax . ' установлен'));
    }
}
$form = new form('?' . passgen());
$form->text('maxfile', __('Макс. размер файла (' . misc::getDataCapacity($blog['file']) . ') '), (int) ($blog['file'] / 1024));
$form->bbcode('* - ' . __('Макс. размер файла указывать в КБ'));
$form->button(__('Сохранить'), 'save');
$form->display();
$doc->ret(__('Блоги'), '/blog/');
