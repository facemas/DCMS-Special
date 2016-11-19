<!DOCTYPE html>
<html ng-app="SocCMS">
    <head>
        <meta charset="utf-8">
        <title><?= $title ?></title>
        <link rel="shortcut icon" href="/sys/images/icons/special.png"/>
        <link rel="stylesheet" href="/sys/themes/.common/animate.css" type="text/css"/>
        <link rel="stylesheet" href="/sys/themes/.common/font-awesome.min.css" type="text/css"/>
        <link rel="stylesheet" href="<?= $path ?>/style.css" type="text/css" charset="utf-8"/>
        <noscript>
        <meta http-equiv="refresh" content="0; URL=/pages/bad_browser.html"/>
        <meta name="theme-color" content="#0084B4" />
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
                <link rel="stylesheet" href="<?= $path ?>/css/image.css" type="text/css"/>

                <nav id="header">
                    <div id="navigation_user">
                        <div class="body_width_limit clearfix">
                            <?php if ($user->group) { ?>
                                <link rel="stylesheet" href="<?= $path ?>/css/transition.css" type="text/css"/>
                                <link rel="stylesheet" href="<?= $path ?>/css/dropdown.css" type="text/css"/>
                                <script charset="utf-8" src="<?= $path ?>/js/transition.min.js" type="text/javascript"></script>
                                <script charset="utf-8" src="<?= $path ?>/js/dropdown.min.js" type="text/javascript"></script>

                                <div class="ui right pointing dropdown" id="profile" style="float: right">
                                    <span data-tooltip='<?= __('Профиль и настройки') ?>' data-position='bottom center'><img class="ui image" src="<?= $user->getAvatar() ?>" style="max-width: 35px; max-height: 35px;border-radius: 3px; "></span>
                                    <div class="menu">
                                        <a class="active item" href="/profile.view.php"><?= __('Мой профиль') ?></a>
                                        <a class="item" href="/menu.user.php"><?= __('Личное меню') ?></a>
                                        <a class="item" href="/profile.edit.php"><?= __('Обновить анкету') ?></a>
                                        <div class="divider"></div>
                                        <a class="item" href="/log.user_aut.php"><?= __('Журнал авторизаций') ?></a>
                                        <div class="item"><i class="fa fa-caret-left icon left"></i> <?= __('Настройки') ?> 
                                            <div class="left menu">
                                                <a class="item" href="/settings.common.php"><?= __('Общие') ?></a>
                                                <a class="item" href="/settings.language.php"><?= __('Язык') ?></a>
                                                <a class="item" href="/settings.private.php"><?= __('Приватность') ?></a>
                                                <a class="item" href="/settings.themes.php"><?= __('Тема оформления') ?></a>
                                                <a class="item" href="/my.avatar.php"><?= __('Обновить аватар') ?></a>
                                                <a class="item" href="/my.fon.php"><?= __('Фон профиля') ?></a>
                                            </div>
                                        </div>
                                        <div class="divider"></div>
                                        <a class="item" href="/exit.php"><?= __('Выйти') ?></a>
                                    </div>
                                </div>
                                <script>
            $('#profile').dropdown();
                                </script>
                            <?php } ?>
                            <a ng-show="+ user.friend_new_count" class='ng-hide link' href='/my.friends.php' ng-bind="str.friends"><?= __("Друзья") ?></a>
                            <a ng-show="+ user.mail_new_count" class='ng-hide link' href='/my.mail.php' ng-bind="str.mail"><?= __("Почта") ?></a>
                            <a ng-show="+ user.not_new_count" class='ng-hide link' href='/my.notification.php' ng-bind="str.notification"><?= __("Уведомления") ?></a>
                            <a ng-hide="+ user.group" class="ng-hide link" href="/login.php?return={{URL}}" ng-bind="translates.auth"><?= __("Авторизация") ?></a>
                            <a ng-hide="+ user.group" class="ng-hide link" href="/reg.php?return={{URL}}" ng-bind="translates.reg"><?= __("Регистрация") ?></a>

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
                            <?= $this->section($err, '<div class="ui floating red message"><p>{text}</p></div>'); ?>
                            <?= $this->section($msg, '<div class="ui floating green message">{text}</div>'); ?>
                            <?= $this->section($info, '<div class="ui floating info message">{text}</div>'); ?>
                        </div>
                        <div class="listing" style="padding: 5px;">
                            <a class="ui basic label" href="/"> <i class="fa fa-home fa-fw"></i> </a> 
                            <?= $this->section($returns, ' <a class="ui basic label" href="{url}">{icon}{name}</a>', true); ?>
                            <?= $this->section($options, ' <a class="ui blue basic label" href="{url}">{icon}{name}</a>', true); ?>
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