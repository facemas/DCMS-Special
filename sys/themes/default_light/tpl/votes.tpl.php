<div class="vote">
    <div class="vote_name"><?= $name ?></div>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <? for ($i = 0; $i < count($votes); $i++) { ?>
            <tr>
                <td colspan="2">
                    <?= $votes[$i]['name'] ?>
                    <?= $votes[$i]['count'] ? ' (' . $votes[$i]['count'] . ')' : '' ?>
                </td>
            </tr>
            <tr>
                <td class="votes">
                    <div class="votes" style=" width:<?= $votes[$i]['pc'] ?>%;">
                        <?= $votes[$i]['pc'] ?>%
                    </div>
                </td>
                <? if ($is_add) { ?>
                    <td class="votes_add">
                        <a href="<?= $votes[$i]['url'] ?>">+</a>
                    </td>
                <? } ?>
            </tr>        
        <? } ?>
    </table>
</div>