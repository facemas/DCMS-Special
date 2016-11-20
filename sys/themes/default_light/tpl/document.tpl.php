<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $lang->xml_lang ?>">
    <head>
        <title><?= $title ?></title>
        <link rel="shortcut icon" href="/sys/images/icons/special.png"/>
        <link rel="stylesheet" href="/sys/themes/.common/font-awesome.min.css" type="text/css"/>
        <meta http-equiv="content-Type" content="application/xhtml+xml; charset=utf-8"/>
        <meta name="generator" content="DCMS Special <?= $dcms->version ?>"/>
        <? if ($description) { ?>
        <meta name="description" content="<?= $description ?>" /><? } ?>
        <? if ($keywords) { ?>
        <meta name="keywords" content="<?= $keywords ?>" /><? } ?>
        <style type="text/css">
            .hide {
                display: none !important;
            }
        </style>
        <link rel="stylesheet" href="<?= $path ?>/style.css" type="text/css"/>
    </head>
    <body class="theme_light theme_light_light">
        <link rel="stylesheet" href="/sys/themes/.common/flag.css" type="text/css"/>
        <script src="https://cdn.jsdelivr.net/emojione/2.2.6/lib/js/emojione.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/emojione/2.2.6/assets/css/emojione.min.css"/>

        <div>
            <? $this->display('inc.title.tpl') ?>
            <? $this->displaySection('after_title')?>
            <? $this->display('inc.user.tpl') ?>

            <?php if ($options) { ?>
                <div id="options">
                    <?= $this->section($options, '<div><a href="{url}">{name}</a></div>'); ?>
                </div>
            <?php } ?>
            <div id="tabs">
                <?= $this->section($tabs, '<a class="tab sel{selected}" href="{url}">{name}</a>', true); ?>
            </div>
            <? $this->displaySection('before_content')?>
            <div id="content">
                <link rel="stylesheet" href="<?= $path ?>/css/message.css" type="text/css"/>

                <div id="messages">
                    <?= $this->section($err, '<div class="ui red message">{text}</div>'); ?>
                    <?= $this->section($msg, '<div class="ui green message">{text}</div>'); ?>
                    <?= $this->section($info, '<div class="ui info message">{text}</div>'); ?>
                </div>
                <?php $this->displaySection('content') ?>
            </div>
            <? $this->displaySection('after_content')?>
            <? $this->display('inc.foot.tpl') ?>
            <div id="foot">
                <?= __("Язык") ?>: <a href='/pages/language.php?return=<?= URL ?>' id="language"><?= $lang->name ?></a><br/>
                <?= __("Время генерации страницы: %s сек", $document_generation_time) ?><br/>
                <?= $copyright ?>
            </div>
        </div>
    </body>
</html>