<?php
$url = new url($link);
$show_pages = array();
for ($i = max(2, $page - 8); $i < min($k_page, $page + 10); $i++) {
    $show_pages[] = $i;
}

/**
 * @param int $page
 * @param int $current
 * @param url $url
 * @return string
 */
function _theme_pages_helper($page, $current, $url) {
    $class = '' . ($page == $current ? 'active item' : 'item');
    return "<a href='{$url->setParam('page', $page)}' class='{$class}'>{$page}</a>";
}
?>

<link rel="stylesheet" href="<?= $path ?>/css/menu.css" type="text/css"/>

<div class="ui pagination menu">
    <?php
    echo _theme_pages_helper(1, $page, $url);
    foreach ($show_pages as $p) {
        echo _theme_pages_helper($p, $page, $url);
    }
    echo _theme_pages_helper($k_page, $page, $url);
    ?>
</div>
