<?php
# Если активирован ui - включаем css файл

if ($ui_image) {
    ?>
    <link rel="stylesheet" href="<?= $path ?>/css/image.css" type="text/css" />
    <?php
}

$classes = array();

if ($class) {
    $classes[] = $class;
}


if ($head) {
    echo $head;
}
?>

<?= ($url ? '<a href="' . $url . '" class="' : '<div class="') . '' . implode(' ', $classes) . '" id="' . $id . '">' ?>
<table>
    <?php
    $img_class = ($image_class ? "class='$image_class'" : null);
    $img_a_class = ($image_a_class ? "<a class='$image_a_class'>" : null);
    # Если активирован comments, то строим для него отдельную структуру
    if ($comments) {
        if ($avatar) {
            echo ($image_a_class ? "<a class='$image_a_class'>" : null) . "<img src='$avatar' alt='' $img_class>" . ($image_a_class ? "</a>" : null);
        }
        ?>
        <div class="content">
            <?php
            if ($img) {
                $i = "<img src='$img' alt=''>";
            } elseif ($icon) {
                $i = "<i class='fa fa-$icon fa-fw'></i>";
            } else {
                $i = null;
            }

            $t = ($time ? " <div class='metadata'><span class='date'>$time</span></div> " : null);


            if ($url) {
                echo "<a class='author' href='$url'>$i $login</a>$t";
            } else {
                echo "<a class='author'>$i $login</a>$t";
            }

            if ($content) {
                echo "<div class='text'><p>$content</p></div>";
            }
            if ($actions) {
                ?>
                <div class='actions'>
                    <?= $this->section($actions, '<a class="reply" href="{url}">{icon} {text}</a>') ?>
                </div>
                <?php
            }
            ?>
        </div>
    <?php } ?>
</table>
<?= $url ? '</a>' : '</div>' ?>
