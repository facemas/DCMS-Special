<?php
# Если активирован ui - включаем css файл

if ($ui_comment) {
    ?>
    <link rel="stylesheet" href="<?= $path ?>/css/comment.css" type="text/css" />
    <?php
}
if ($ui_image) {
    ?>
    <link rel="stylesheet" href="<?= $path ?>/css/image.css" type="text/css" />
    <?php
}
if ($ui_header) {
    ?>
    <link rel="stylesheet" href="<?= $path ?>/css/header.css" type="text/css" />
    <?php
}
if ($ui_list) {
    ?>
    <link rel="stylesheet" href="<?= $path ?>/css/list.css" type="text/css" />
    <?php
}
if ($ui_feed) {
    ?>
    <link rel="stylesheet" href="<?= $path ?>/css/feed.css" type="text/css" />
    <?php
}
if ($ui_segment) {
    ?>
    <link rel="stylesheet" href="<?= $path ?>/css/segment.css" type="text/css" />
    <?php
}

$classes = array('ui');
if ($class) {
    $classes[] = $class;
}
?>

<div class="<?= implode(' ', $classes) ?>" id="<?= $id ?>" data-form-id="<?= $form ? $form->id : '' ?>" data-ajax-url="<?= $ajax_url ?>">
    <?= $content ?>
</div>