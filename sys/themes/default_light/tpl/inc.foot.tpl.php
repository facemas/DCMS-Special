<?php if ($actions) { ?>
    <div id="actions">
        <?= $this->section($actions, '<div><a href="{url}">{name}</a></div>'); ?>
    </div>
<?php } ?>

<?php if ($returns OR ! IS_MAIN) { ?>
    <div id="returns">        
        <?= $this->section($returns, '<div><a href="{url}">{name}</a></div>'); ?>
        <?php if (!IS_MAIN) { ?>
            <div><a href='/'><?= __("На главную") ?></a></div>
        <?php } ?>  
    </div>
<?php } ?>


