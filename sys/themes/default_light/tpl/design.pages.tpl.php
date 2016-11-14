<link rel="stylesheet" href="<?= $path ?>/css/menu.css" type="text/css"/>

<div class="ui pagination menu">
    <?php
    echo $page == 1 ? '<span class="item active">1</span>' : '<a href="' . $link . 'page=1" class="item">1</a>';
    for ($i = max(2, $page - 4); $i < min($k_page, $page + 3); $i++) {
        if ($i == $page) {
            echo '<span class="item active">' . $i . '</span>';
        } else {
            echo '<a href="' . $link . 'page=' . $i . '" class="item">' . $i . '</a>';
        }
    }
    echo $page == $k_page ? '<span class="item active">' . $k_page . '</span>' : '<a href="' . $link . 'page=' . $k_page . '" class="item">' . $k_page . '</a>'
    ?>
</div>
