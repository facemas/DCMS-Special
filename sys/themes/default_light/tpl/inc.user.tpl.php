<div id="navigation_user">
    <?
    if ($user->id) {
        ?>
        <? if ($user->friend_new_count) { ?>
            <a id='user_friend' href='/my.friends.php'><?= __("Друзья") ?> +<span><?= $user->friend_new_count ?></span></a>
        <? } ?>
        <? if ($user->mail_new_count) { ?>
            <a id='user_mail' href='/my.mail.php?only_unreaded'><?= __("Почта") ?> +<span><?= $user->mail_new_count ?></span></a>
        <? } ?>
        <a id='menu_user' style='font-weight: bold;' href="/menu.user.php"><?= $user->login ?></a> 

        <?
    } else {
        ?>
        <a href="/login.php?return=<?= URL ?>"><?= __("Авторизация") ?></a>
        <a href="/reg.php?return=<?= URL ?>"><?= __("Регистрация") ?></a>
        <?
    }
    ?>
</div> 