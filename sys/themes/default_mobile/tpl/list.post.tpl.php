<?php
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
    if ($time) {
        $t = ($time ? " <div class='metadata'><span class='date'>$time</span></div> " : null);
    }
    if ($image) {
        $img_class = ($image_class ? "class='$image_class'" : null);
        $img_a_class = ($image_a_class ? "<a class='$image_a_class'>" : null);
        echo ($image_a_class ? "<span class='$image_a_class'>" : null) . "<img src='$image' alt='' $img_class>" . ($image_a_class ? "</span>" : null);
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

        if ($url) {
            echo "<a class='author' href='$url'>$i $login $title</a>$t";
        } else {
            echo "<span class='header'>$i $login $title</span>$t";
        }
        ?>
        <?= $content ?>
    </div>
</table>
<?= $url ? '</a>' : '</div>' ?>
