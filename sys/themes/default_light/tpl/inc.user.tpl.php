<div id="navigation_user">
    <?php
    if ($user->id) {
        ?>
        <?php if ($user->friend_new_count) { ?>
            <a id='user_friend' href='/my.friends.php'><?= __("Друзья") ?> +<span><?= $user->friend_new_count ?></span></a>
        <?php } ?>
        <?php if ($user->mail_new_count) { ?>
            <a id='user_mail' href='/my.mail.php?from=new'><?= __("Почта") ?> +<span><?= $user->mail_new_count ?></span></a>
        <?php } ?>
        <a id='menu_user' style='font-weight: bold;' href="/profile.view.php"><?= $user->login ?></a> 

        <?php
    } else {
        ?>
        <a href="/login.php?return=<?= URL ?>"><?= __("Авторизация") ?></a>
        <a href="/reg.php?return=<?= URL ?>"><?= __("Регистрация") ?></a>
        <?php
    }
    ?>
</div> 