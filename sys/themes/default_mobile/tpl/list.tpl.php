<?php

$classes = array('ui');
if ($class) {
    $classes[] = $class;
}
?>

<div class="<?= implode(' ', $classes) ?>" id="<?= $id ?>" data-form-id="<?= $form ? $form->id : '' ?>" data-ajax-url="<?= $ajax_url ?>">
    <?= $content ?>
</div>