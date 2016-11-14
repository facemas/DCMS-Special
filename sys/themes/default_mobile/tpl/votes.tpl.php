<div class="votes">
    <span class="votes_name"><?= $name ?></span>
    <table style="width: 100%">
        <?
        foreach ($votes AS $vote) {
            ?>
            <tr>
                <td class="vote_name" colspan="2">
                    <?= $vote['name'] ?>
                    <?= $vote['count'] ? ' (' . $vote['count'] . ')' : '' ?>
                </td>
            </tr>
            <tr style="height: 16px;">
                <td class="vote_container" style="width: 100%">
                    <div class="vote_scale" style="<?= 'width: ' . max($vote['pc'], 6) . '%' ?>">
                        <?= $vote['pc'] ?>%
                    </div>
                </td>
                <? if ($is_add) { ?>
                    <td>
                        <a class="vote_plus" href="<?= $vote['url'] ?>">+</a>
                    </td>
                <? } ?>
            </tr>        
        <? } ?>
    </table>
</div>