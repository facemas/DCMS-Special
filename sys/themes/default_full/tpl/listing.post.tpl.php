<?php
$classes = array('post', 'clearfix');
if ($highlight) {
    $classes[] = 'highlight';
}
if ($image) {
    $classes[] = 'image';
}
if ($icon || $img) {
    $classes[] = 'icon';
}
if ($time) {
    $classes[] = 'time';
}
if ($actions) {
    $classes[] = 'actions';
}
if ($counter) {
    $classes[] = 'counter';
}
if ($bottom) {
    $classes[] = 'bottom';
}
if ($content) {
    $classes[] = 'content';
}

$post_counter = $counter ? '<span class="ui basic label" style="float: right;margin-left: 10px;">' . $counter . '</span>' : '';
?>
<div id="<?= $id ?>" class="<?= implode(' ', $classes) ?>" data-ng-controller="ListingPostCtrl" data-post-url="<?= $url ?>">
    <div class="post_image"><img src="<?= $image ?>" alt=""></div>
    <div class="post_head">
        <span class="post_icon">
            <?php
            if ($img) {
                echo "<img src='$img' />";
            } elseif ($icon) {
                echo "<i class='fa fa-$icon fa-fw'></i>";
            } else {
                echo "<i class='fa fa-$icon fa-fw'></i>";
            }
            ?>
        </span>
        <a class="post_title" <?php if ($url) { ?>href="<?= $url ?>"<?php } ?>><?= $title ?></a>
        <span class="post_actions"><?= $this->section($actions, '<a href="{url}"><i class="fa fa-{icon} fa-fw"></i></a>')
            ?></span>

        <?= $post_counter ?>

        <span class="post_time"><?= $time ?></span>
    </div>
    <div class="post_content"><?= $content ?></div>
    <div class="post_bottom"><?= $bottom ?></div>
</div>