<!DOCTYPE html>
<html ng-app="SocCMS">
    <head>
        <title><?= $title ?></title>
        <link rel="shortcut icon" href="/sys/images/icons/special.png"/>
        <link rel="stylesheet" href="/sys/themes/.common/animate.css" type="text/css"/>
        <link rel="stylesheet" href="/sys/themes/.common/font-awesome.min.css" type="text/css"/>
        <link rel="stylesheet" href="<?= $path ?>/style.css" type="text/css"/>
        <noscript>
        <meta http-equiv="refresh" content="0; URL=/pages/bad_browser.html"/>
        </noscript>
        <script>
            (function () {
            var getIeVer = function () {
            var rv = - 1; // Return value assumes failure.
            if (navigator.appName === 'Microsoft Internet Explorer') {
            var ua = navigator.userAgent;
            var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
            if (re.exec(ua) !== null)
                    rv = parseFloat(RegExp.$1);
            }
            return rv;
            };
            var ver = getIeVer();
            if (ver !== - 1 && ver < 9) {
            window.location.href = "/pages/bad_browser.html";
            }
            })();
        </script>
        <script charset="utf-8" src="/sys/themes/.common/jquery-3.1.1.min.js" type="text/javascript"></script>
        <script charset="utf-8" src="/sys/themes/.common/angular.js" type="text/javascript"></script>
        <script charset="utf-8" src="/sys/themes/.common/angular-animate.js" type="text/javascript"></script>
        <script charset="utf-8" src="/sys/themes/.common/dcmsApi.js" type="text/javascript"></script>
        <script charset="utf-8" src="/sys/themes/.common/elastic.js" type="text/javascript"></script>
        <script charset="utf-8" src="<?= $path ?>/js.js" type="text/javascript"></script>

        <meta name="generator" content="DCMS Special <?= dcms::getInstance()->version ?>"/>
        <? if ($description) { ?>
        <meta name="description" content="<?= $description ?>"/>
        <? } ?>
        <? if ($keywords) { ?>
        <meta name="keywords" content="<?= $keywords ?>"/>
        <? } ?>
        <script>
            user = <?= json_encode(current_user::getInstance()->getCustomData(array('id', 'group', 'mail_new_count', 'not_new_count', 'friend_new_count', 'nick'))) ?>;
            translates = {
            bbcode_b: '<?= __('Текст жирным шрифтом') ?>',
                    bbcode_i: '<?= __('Текст курсивом') ?>',
                    bbcode_u: '<?= __('Подчеркнутый текст') ?>',
                    bbcode_img: '<?= __('Вставка изображения') ?>',
                    bbcode_php: '<?= __('Выделение PHP-кода') ?>',
                    bbcode_big: '<?= __('Увеличенный размер шрифта') ?>',
                    bbcode_small: '<?= __('Уменьшенный размер шрифта') ?>',
                    bbcode_gradient: '<?= __('Цветовой градиент') ?>',
                    bbcode_hide: '<?= __('Скрытый текст') ?>',
                    bbcode_spoiler: '<?= __('Свернутый текст') ?>',
                    smiles: '<?= __('Смайлы') ?>',
                    form_submit_error: '<?= __('Ошибка связи...') ?>',
                    auth: '<?= __("Авторизация") ?>',
                    reg: '<?= __("Регистрация") ?>',
                    friends: '<?= __("Друзья") ?>',
                    mail: '<?= __("Почта") ?>',
                    notification: '<?= __("Уведомления") ?>',
                    error: '<?= __('Неизвестная ошибка') ?>',
                    rating_down_message: '<?= __('Подтвердите понижение рейтинга сообщения.') . (dcms::getInstance()->forum_rating_down_balls ? "\\n" . __('Будет списано баллов: %s', dcms::getInstance()->forum_rating_down_balls) : '') ?>'
            };
            codes = [
            {Text: 'B', Title: translates.bbcode_b, Prepend: '[b]', Append: '[/b]'},
            {Text: 'I', Title: translates.bbcode_i, Prepend: '[i]', Append: '[/i]'},
            {Text: 'U', Title: translates.bbcode_u, Prepend: '[u]', Append: '[/u]'},
            {Text: 'BIG', Title: translates.bbcode_big, Prepend: '[big]', Append: '[/big]'},
            {Text: 'Small', Title: translates.bbcode_small, Prepend: '[small]', Append: '[/small]'},
            {Text: 'IMG', Title: translates.bbcode_img, Prepend: '[img]', Append: '[/img]'},
            {Text: 'PHP', Title: translates.bbcode_php, Prepend: '[php]', Append: '[/php]'},
            {Text: 'SPOILER', Title: translates.bbcode_spoiler, Prepend: '[spoiler title=""]', Append: '[/spoiler]'},
            {Text: 'HIDE', Title: translates.bbcode_hide, Prepend: '[hide group="1" balls="1"]', Append: '[/hide]'}
            ];
        </script>
        <style type="text/css">
            .ng-hide {
                display: none !important;
            }
        </style>
    </head>
    <body class="theme_light" ng-controller="SocCMS">
        <audio id="audio_notify" preload="auto" class="ng-hide">
            <source src="/sys/themes/.common/notify.mp3" />
            <source src="/sys/themes/.common/notify.ogg" />
        </audio>
        <div id="main">
            <div id="top_part">
                <nav id="header" class="navbar navbar-light" style="background-color: #fff;border-bottom: #e1e2e3 1px solid;">
                    <div id="navigation_user">
                        <div class="body_width_limit clearfix">
                            <a ng-show="+ user.group" class="<?= $user->group ? '' : 'ng-hide' ?>" href="/profile.view.php" ng-bind="user.nick"><?= $user->nick ?></a>
                            <?php if ($user->group > 0) { ?>
                                <a class="action" href="/menu.user.php"><i class="fa fa-cogs fa-fw"></i> <?= __("Личное меню") ?></a>
                            <?php } ?>
                            <a ng-show="+ user.friend_new_count" class='ng-hide' href='/my.friends.php' ng-bind="str.friends"><?= __("Друзья") ?></a>
                            <a ng-show="+ user.mail_new_count" class='ng-hide' href='/my.mail.php?only_unreaded' ng-bind="str.mail"><?= __("Почта") ?></a>
                            <a ng-show="+ user.not_new_count" class='ng-hide' href='/my.notification.php' ng-bind="str.notification"><?= __("Уведомления") ?></a>
                            <a ng-hide="+ user.group" class="ng-hide" href="/login.php?return={{URL}}" ng-bind="translates.auth"><?= __("Авторизация") ?></a>
                            <a ng-hide="+ user.group" class="ng-hide" href="/reg.php?return={{URL}}" ng-bind="translates.reg"><?= __("Регистрация") ?></a>

                            <?= $this->section($actions, ' <a class="action" href="{url}">{icon}{name}</a>'); ?>

                        </div>
                    </div>
                    <?php $this->displaySection('header'); ?>
                </nav>
                <link rel="stylesheet" href="/sys/themes/.common/flag.css" type="text/css"/>
                <div class="body_width_limit clearfix">
                    <div id="left_column">
                        <?php $this->displaySection('left_column'); ?>
                    </div>
                    <div id="content">
                        <link rel="stylesheet" href="<?= $path ?>/css/message.css" type="text/css"/>
                        <link rel="stylesheet" href="<?= $path ?>/css/popup.css" type="text/css"/>

                        <div id="messages">
                            <?= $this->section($err, '<div class="ui red message">{text}</div>'); ?>
                            <?= $this->section($msg, '<div class="ui green message">{text}</div>'); ?>
                        </div>
                        <div class="listing" style="padding: 5px;">
                            <a class="btn btn-secondary btn-sm" href="/"> <i class="fa fa-home fa-fw"></i> </a> 
                            <?= $this->section($returns, ' <a class="btn btn-secondary btn-sm" href="{url}">{icon}{name}</a>', true); ?>
                            <?= $this->section($options, ' <a class="btn btn-grey btn-sm" href="{url}">{icon}{name}</a>', true); ?>
                        </div>
                        <div id="tabs" class="<?= !$tabs ? 'ng-hide' : '' ?>">
                            <?= $this->section($tabs, '<a class="tab sel{selected}" href="{url}">{name}</a>', true); ?>
                        </div>
                        <?php $this->displaySection('content'); ?>
                    </div>
                </div>
                <div id="empty"></div>
            </div>
            <div id="footer">
                <div class="body_width_limit">
                    <span id="copyright">
                        <?= $copyright ?>
                    </span>
                    <span id="language">
                        <?= __("Язык") ?>:<a href='/language.php?return={{URL}}'><i class="<?= $lang->icon ?> flag"></i> <?= $lang->name ?></a>
                    </span>
                    <span id="generation">
                        <?= __("Время генерации страницы: %s сек", $document_generation_time) ?>
                    </span>
                </div>
            </div>
        </div>
    </body>
</html>