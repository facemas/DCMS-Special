<!DOCTYPE html>
<html>
    <head>
        <title><?= $title ?></title>
        <link rel="shortcut icon" href="/sys/images/icons/special.png"/>
        <link rel="stylesheet" href="/sys/themes/.common/font-awesome.min.css" type="text/css"/>
        <link rel="stylesheet" href="<?= $path ?>/res/style.css" type="text/css"/>
        <meta name="viewport" content="minimum-scale=1.0,initial-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
        <meta name="generator" content="SocCMS <?= $dcms->version ?>"/>

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
            <source src="/sys/themes/.common/notify.ogg"/>
        </audio>
        <div id="container_content">
            <link rel="stylesheet" href="<?= $path ?>/css/menu.css" type="text/css"/>
            <link rel="stylesheet" href="<?= $path ?>/css/dropdown.css" type="text/css"/>
            <link rel="stylesheet" href="/sys/themes/.common/flag.css" type="text/css"/>

            <header id='title' class="<?= $returns ? 'ui pointing fluid menu returns' : 'ui pointing fluid menu' ?>">

                <a class="<?= ($head == 'home' ? 'item active' : 'item') ?>" href="/"><i class="fa fa-home fa-lg"></i></a>
                <span class="tIcon left"><div class="ui dropdown item"><i class="fa fa-chevron-left fa-lg"></i> 
                        <div class="menu">
                            <?= $this->section($returns, '<a href="{url}" class="item"><i class="fa fa-chevron-left fa-lg"></i> {name}</a>', true); ?>
                        </div>
                    </div>
                </span>

                <?php if ($user->group) { ?>
                    <span class="tIcon mail"><a class="<?= ($head == 'mail' ? 'item active' : 'item') ?>" href="/my.mail.php"><i class="fa fa-envelope fa-lg" style="color: orange"></i></a></span>
                    <span class="tIcon notification"><a class="<?= ($head == 'notification' ? 'item active' : 'item') ?>" href="/my.notification.php"><i class="fa fa-bell fa-lg" style="color: orange"></i></a></span>
                    <span class="tIcon friend"><a class="<?= ($head == 'friends' ? 'item active' : 'item') ?>" href="/my.friends.php"><i class="fa fa-user-plus fa-lg" style="color: orange"></i></a></span>

                <?php } ?>

                <div class="itemtext"><?= $title ?></div>

                <div class="right menu">
                    <?php if ($user->group) { ?>
                        <a class="<?= ($head == 'profile' ? 'item active' : 'item') ?>" href="/profile.view.php"><i class="fa fa-user-circle-o fa-lg"></i></a>
                    <?php } else { ?>
                        <a class="item" href="/login.php?return=<?= URL ?>"><i class="fa fa-sign-in fa-lg"></i></a>
                        <a class="item" href="/reg.php?return=<?= URL ?>"><i class="fa fa-user-plus fa-lg"></i></a>
                    <?php } ?>
                </div>
            </header>


            <?php $this->displaySection('after_title') ?>
            <?php if ($options) { ?>
                <div id="options">
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

                <div id="messages">
                    <link rel="stylesheet" href="<?= $path ?>/css/message.css" type="text/css"/>
                    <?= $this->section($err, '<div class="ui red message">{text}</div>'); ?>
                    <?= $this->section($msg, '<div class="ui green message">{text}</div>'); ?>
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
    </body>
</html>