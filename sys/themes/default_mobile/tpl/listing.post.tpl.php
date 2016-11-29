
<?php
$post_time = $time ? '<span class="time">' . $time . '</span>' : '';
$post_counter = $counter ? '<span class="ui basic label" style="float: right">' . $counter . '</span>' : '';
$post_actions = '<span class="actions">' . $this->section($actions, '<a href="{url}"><i class="fa fa-{icon} fa-fw"></i></a>') . '</span>';
?>
<?= ($url ? '<a href="' . $url . '" class="' : '<div class="') . 'post' . ($highlight ? ' highlight' : '') . '" id="' . $id . '">' ?>
<table cellspacing="0" cellpadding="0" width="100%">
<?php
if ($image) {
    ?>
        <tr>
            <td class="image" rowspan="4">
                <img src="<?= $image ?>" alt=""/>
            </td>
            <td class="title">
    <?= $title ?>
    <?= $post_counter ?>
            </td>

            <td class="right">
    <?= $post_time ?>
    <?= $post_actions ?>
            </td>
        </tr>
                <?php
            } elseif ($icon || $img) {
                ?>
        <tr>
            <td class="icon">
        <?php
        if ($img) {
            echo "<img src='$img' />";
        } elseif ($icon) {
            echo "<i class='fa fa-$icon fa-fw'></i>";
        } else {
            echo "<img src='$icon' />";
        }
        ?>
            </td>
            <td class="title">
                <?= $title ?>
    <?= $post_counter ?>
            </td>

            <td class="right">
    <?= $post_time ?>
    <?= $post_actions ?>
            </td>
        </tr>
                <?php
            } else {
                ?>
        <tr>
            <td class="title">
        <?= $title ?>
    <?= $post_counter ?>
            </td>

            <td class="right">
    <?= $post_time ?>
    <?= $post_actions ?>
            </td>
        </tr>
            <?php } ?>

    <?php if ($content) { ?>
        <tr>
            <td class="content" colspan="10">
        <?= $content ?>
            </td>
        </tr>
            <?php } ?>

    <?php if ($bottom) { ?>
        <tr>
            <td class="bottom" colspan="10">
        <?= $bottom ?>
            </td>
        </tr>
            <?php } ?>
</table>
    <?=
    $url ? '</a>' : '</div>'?>