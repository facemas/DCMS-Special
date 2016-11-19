<!DOCTYPE html>
<html>
    <head>
        <title><?= $title ?></title>
        <link rel="shortcut icon" href="/sys/images/icons/special.png"/>
        <link rel="stylesheet" href="/sys/themes/.common/font-awesome.min.css" type="text/css"/>
        <link rel="stylesheet" href="<?= $path ?>/res/style.css" type="text/css"/>
        <meta name="viewport" content="minimum-scale=1.0,initial-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
        <meta name="generator" content="SocCMS <?= $dcms->version ?>"/>
        <meta name="theme-color" content="#0084B4" />

        <? if ($description) { ?>
        <meta name="description" content="<?= $description ?>" /><? } ?>
        <? if ($keywords) { ?>
        <meta name="keywords" content="<?= $keywords ?>" /><? } ?>

        <script>
            window.translate = {
                'friends': "<?= __("Друзья") ?>",
                'mail': "<?= __("Почта") ?>",
                'notification': "<?= __("Уведомления") ?>",
                'user_menu': "<?= __("Личное меню") ?>",
                'auth': "<?= __("Авторизация") ?>",
                'reg': "<?= __("Регистрация") ?>",
                'rating_down_message': '<?= __('Подтвердите понижение рейтинга сообщения.') . ($dcms->forum_rating_down_balls ? "\\n" . __('Будет списано баллов: %s', $dcms->forum_rating_down_balls) : '') ?>'
            };

            window.user = <?= json_encode($user->getCustomData(array('id', 'group', 'friend_new_count', 'mail_new_count', 'not_new_count', 'login'))) ?>;
            window.URL = "<?= URL ?>";
        </script>
        <script src="/sys/themes/.common/jquery-3.1.1.min.js"></script>
        <script src="/sys/themes/.common/dcmsApi.js"></script>
        <script src="<?= $path ?>/res/inputInsert.js"></script>
        <script src="<?= $path ?>/res/user.js"></script>
        <script src="<?= $path ?>/res/common.js"></script>
        <script src="<?= $path ?>/res/ajaxForm.js" async="async"></script>
        <script src="<?= $path ?>/res/smiles.js" async="async"></script>
        <script src="<?= $path ?>/res/listing.js" async="async"></script>
    </head>

    <body class="">
        <audio id="audio_notify">
            <source src="/sys/themes/.common/notify.mp3"/>
            <source src="/sys/themes/.common/notify.m4r"/>
            <source src="/sys/themes/.common/notify.aac"/>
            <source src="/sys/themes/.common/notify.ogg"/>
        </audio>

        <link rel="stylesheet" href="<?= $path ?>/css/menu.css" type="text/css"/>
        <link rel="stylesheet" href="<?= $path ?>/css/popup.css" type="text/css"/>
        <link rel="stylesheet" href="<?= $path ?>/css/image.css" type="text/css"/>
        <link rel="stylesheet" href="/sys/themes/.common/flag.css" type="text/css"/>
        <script src="<?= $path ?>/res/sidebar.min.js"></script>
        <link rel="stylesheet" href="<?= $path ?>/css/sidebar.min.css" type="text/css"/>

        <header id='title' class="ui secondary pointing fluid menu">

            <a class="<?= ($head == 'home' ? 'item active' : 'item') ?>" id="home"><i class="fa fa-bars fa-lg"></i></a>

            <?php if ($user->group) { ?>
                <span class="tIcon mail"><a class="<?= ($head == 'mail' ? 'item active' : 'item') ?>" href="/my.mail.php"><span class="blink"><i class="fa fa-envelope fa-lg"></i></span></a></span>
                <span class="tIcon notification" id="blink"><a class="<?= ($head == 'notification' ? 'item active' : 'item') ?>" href="/my.notification.php"><span class="blink"><i class="fa fa-bell fa-lg"></i></span></a></span>
                <span class="tIcon friend"><a class="<?= ($head == 'friends' ? 'item active' : 'item') ?>" href="/my.friends.php"><span class="blink"><i class="fa fa-user-plus fa-lg"></i></span></a></span>

            <?php } ?>

            <div class="itemtext"><?= $title ?></div>

            <div class="right menu">
                <?php if ($user->group) { ?>
                    <a class="<?= ($head == 'profile' ? 'item active' : 'item') ?>" href="/profile.view.php" style="padding: 0.4em 0.6em;"><img class="ui avatar image" src="<?= $user->getAvatar() ?>"></a>
                <?php } else { ?>
                    <a class="item" href="/login.php?return=<?= URL ?>"><i class="fa fa-sign-in fa-lg"></i></a>
                    <a class="item" href="/reg.php?return=<?= URL ?>"><i class="fa fa-user-plus fa-lg"></i></a>
                <?php } ?>
            </div>
        </header>


        <div class="ui left sidebar" style="margin-top: 50px;">
            <div class="ui vertical menu" style="border-radius: 2px;width: 260px;">
                <a class="item" href="/"><i class="fa fa-home fa-fw"></i>
                    <?= __('Главная') ?>
                </a>
            </div>
            <?php $this->displaySection('menu') ?>
        </div>
        <div class="pusher">
            <div id="container_content">
                <?php $this->displaySection('after_title') ?>

                <?php if (!$options) { ?>
                    <span class="<?= $returns ? 'returns tIcon left' : 'tIcon left' ?>">

                        <script src="<?= $path ?>/res/dropdown.min.js"></script>
                        <script src="<?= $path ?>/res/transition.min.js"></script>
                        <link rel="stylesheet" href="<?= $path ?>/css/dropdown.css" type="text/css"/>
                        <link rel="stylesheet" href="<?= $path ?>/css/transition.css" type="text/css"/>

                        <div class="mini ui icon top left pointing dropdown button" id="hybrid" style="border: 1px solid #e1e8ed; background: #fff; margin-left: 5px; font-size: 0.875rem;line-height: 1.25;text-align: center;white-space: nowrap;padding: 0.25rem 0.5rem;">
                            <i class="fa fa-map-signs" style="margin: 0;"></i>
                            <div class="menu">
                                <div class="header"><?= __('Навигация') ?></div>
                                <?= $this->section($returns, '<div class="item"><a href="{url}">{name}</a></div>', true); ?>
                            </div>
                        </div>
                        <script>
                $('#hybrid').dropdown();
                        </script>
                    </span>
                <?php } ?>

                <?php if ($options) { ?>
                    <div id="options">
                        <span class="<?= $returns ? 'returns tIcon left' : 'tIcon left' ?>">

                            <script src="<?= $path ?>/res/dropdown.min.js"></script>
                            <script src="<?= $path ?>/res/transition.min.js"></script>
                            <link rel="stylesheet" href="<?= $path ?>/css/dropdown.css" type="text/css"/>
                            <link rel="stylesheet" href="<?= $path ?>/css/transition.css" type="text/css"/>

                            <div class="mini ui icon top left pointing dropdown button" id="hybrid" style="border: 1px solid #e1e8ed; background: #fff; margin-left: 5px; font-size: 0.875rem; line-height: 1.25; text-align: center; white-space: nowrap;   padding: 0.25rem 0.5rem;">
                                <i class="fa fa-map-signs" style="margin: 0;"></i>
                                <div class="menu">
                                    <div class="header"><?= __('Навигация') ?></div>
                                    <?= $this->section($returns, '<div class="item"><a href="{url}" style="border: 0;margin-left: -5px;padding: 0;">{name}</a></div>', true); ?>
                                </div>
                            </div>
                            <script>
                $('#hybrid').dropdown();
                            </script>
                        </span>
                        <?= $this->section($options, '<a class="gradient_blue border" href="{url}">{name}</a>'); ?>

                    </div>
                <?php } ?>
                <?php if ($tabs) { ?>
                    <div id="tabs">
                        <?= $this->section($tabs, '<a class="tab sel{selected}" href="{url}">{name}</a>', true); ?>
                    </div>
                <?php } ?>
                <?php $this->displaySection('before_content') ?>
                <section id="content">

                    <link rel="stylesheet" href="<?= $path ?>/css/message.css" type="text/css"/>
                    <div id="messages">
                        <?= $this->section($err, '<div class="ui floating red message"><p>{text}</p></div>'); ?>
                        <?= $this->section($msg, '<div class="ui floating green message"><p>{text}</p></div>'); ?>
                        <?= $this->section($info, '<div class="ui floating info message"><p>{text}</p></div>'); ?>
                    </div>
                    <?php $this->displaySection('content') ?>
                </section>
                <?php $this->displaySection('after_content') ?>
                <?php $this->display('inc.foot.tpl') ?>
                <footer id="footer">
                    <?=
                    /** @var string $document_generation_time */
                    __("Время генерации страницы: %s сек", $document_generation_time)
                    ?><br/>
                    <?= $copyright ?>
                </footer>
            </div>
            <script>
                $('.ui.sidebar').first()
                        .sidebar('attach events', '#home')
                        .sidebar('setting', 'transition', 'overlay')
                        ;
                $('#home')
                        .removeClass('disabled')
                        ;
            </script>
        </div>
    </body>
</html>
