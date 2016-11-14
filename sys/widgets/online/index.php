<?php

# Обычный виджет вывода онлайн пользователей
defined('SOCCMS') or die;

global $user, $dcms;
$db = DB::me();

$res = $db->query("SELECT COUNT(*) FROM `users_online`");
$online = $res->fetchColumn();

$res = $db->query("SELECT COUNT(*) FROM `guest_online` WHERE `conversions` >= '5' AND `is_robot` = '0'");
$guest = $res->fetchColumn();

# Для вывода аватаров
$user_limit = ($dcms->browser_type == 'full' ? '10' : '5');

echo "<div class='listing'>";

echo "<div class='post clearfix highlight'>";
echo "<center><span class='center_text'>";
echo "<a href='/online.users.php' class='post_title'>" . __('Онлайн %s', $online) . "</a>";
echo " &#183; ";
echo "<a href='/online.guest.php' class='post_title'>" . __('Гостей %s', $guest) . "</a>";
echo "</span></center>";
echo "<div class='hr'></div>";
echo "</div>";

if ($dcms->widget_items_count) {
    $q = $db->query("SELECT `users_online`.* , `browsers`.`name` AS `browser` FROM `users_online` LEFT JOIN `browsers` ON `users_online`.`id_browser` = `browsers`.`id` ORDER BY `users_online`.`time_login` DESC LIMIT $user_limit");

    echo "<div class='post image clearfix'>";
    if ($arr = $q->fetchAll()) {
        foreach ($arr AS $ank) {
            $user_on = new user($ank['id_user']);

            echo "<span class='hint--right' aria-label='$user_on->nick'><div class='post_image'><a href='/profile.view.php?id=$user_on->id'><img src='" . $user_on->getAvatar() . "' width='48' height='48' /></a></div></span>";
        }
    }
    echo "</div>";
}

echo "</div>";


