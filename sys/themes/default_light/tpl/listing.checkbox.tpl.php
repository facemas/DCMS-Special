<?
$div = 'post gradient_grey border padding ' . ($highlight ? 'post_hightlight' : '');
$post_time = $time ? '<span class="post_time">' . $time . '</span>' : '';
$post_counter = $counter ? '<span class="post_counter">' . $counter . '</span>' : '';
$checked_st = $checked ? ' checked="checked"' : '';
?>
<div class="<?= $div ?>">
    <label for="<?= $name ?>">
        <table cellspacing="0" cellpadding="0" width="100%">

            <tr>
                <td style="width:16px">
                    <input type="checkbox" id="<?= $name ?>" name="<?= $name ?>" <?= $checked_st ?> />
                </td>
                <td class="title">
                    <?= $title ?>
                </td>
                <?= $post_time ?>
                <?= $post_counter ?>
            </tr>


            <? if ($content) { ?>
                <tr>
                    <td class="content" colspan="10">
                        <?= $content ?>
                    </td>
                </tr>
            <? } ?>

            <? if ($bottom) { ?>
                <tr>
                    <td class="bottom" colspan="10">
                        <?= $bottom ?>
                    </td>
                </tr>
            <? } ?>
        </table>
    </label>
</div>
