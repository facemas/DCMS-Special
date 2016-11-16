<div class="el_profile">
    <div style="overflow: hidden;height: 180px;">
        <?php
        if ($fon_create[0]) {
            echo "<a href='$fon_create[0]' class='addfon'>$fon_create[1]</a>";
        }
        ?>
        <!-- Выводим фон профиля -->
        <img class="profile" style="background: #1d2129 url(<?= $fon ?>) no-repeat center; background-size: 100% 100%; -webkit-background-size: 100% 100%; padding-bottom: 56.25%;" />
    </div>

    <div style="display: inline-block;position: relative;vertical-align: bottom;text-align: center;margin-left: 2em;">
        <?php
        if ($avatar[0]) {
            # Выводим кнопку - изменить аватар
            echo "<a href='$avatar[0]' class='addavatar'>$avatar[1]</a>";
        }
        ?>
        <!-- Выводим аватар -->
        <img class="avatar_img" src="<?= $avatar[0] ?>" style="width: 150px; height: 150px; vertical-align: top;" />
        <!-- Выводим кнопту комментариев аватара -->
        <span class="comments">
            <?php
            if ($comments_avatar[0]) {
                echo "<a href='$comments_avatar[0]' class='comments'>$comments_avatar[1]</a>";
            }
            ?>
        </span>  
        <!-- Выводим кнопку лайков аватара -->
        <span class="like">
            <?php
            if ($like_avatar[0]) {
                echo "<a href='$like_avatar[0]' class='like'>$like_avatar[1]</a>";
            }
            if ($like_all_avatar[0]) {
                echo "<a href='$like_all_avatar[0]' class='like'>$like_all_avatar[1]</a>";
            }
            ?>
        </span>
    </div>
    <div class="block">
        <!-- Выводим логин -->
        <h3 class="nick"><?= $login ?> <span class="<?= $online[0] ?>"><?= $online[1] ?></span></h3>
        <!-- Выводим статус -->
        <div class="d_r"><?= $group_name ?></div>

    </div>
    <div class="action">
        <!-- Выводим действия пользователя -->
        <?if ($gifts[0]){?><a href="<?= $gifts[0] ?>" class="act"><?= $gifts[1] ?></a><?}?>
        <?if ($mess[0]){?><a href="<?= $mess[0] ?>" class="act"><?= $mess[1] ?></a><?}?>
        <?if ($balls[0]){?><a href="<?= $balls[0] ?>" class="act"><?= $balls[1] ?></a><?}?>
        <?if ($friend[0]){?><a href="<?= $friend[0] ?>" class="act"><?= $friend[1] ?></a><?}?>
        <?if ($profile_edit[0]){?><a href="<?= $profile_edit[0] ?>" class="act"><?= $profile_edit[1] ?></a><?}?>
    </div>
</div>
