<?php

# Обычный виджет вывода онлайн пользователей
defined('SOCCMS') or die;

global $user, $dcms;
$db = DB::me();

$res = $db->query("SELECT COUNT(*) FROM `users_online`");
$online = $res->fetchColumn();

$res = $db->query("SELECT COUNT(*) FROM `guest_online` WHERE `conversions` >= '5' AND `is_robot` = '0'");
$guest = $res->fetchColumn();

# Для вывода кол-ва аватаров
$user_limit = ($dcms->browser_type == 'full' ? '10' : '5');

$listing = new ui_components();
$listing->ui_segment = true; //подключаем css segment
$listing->ui_divider = true; //подключаем css divider
$listing->ui_list = true; //подключаем css list
$listing->class = $dcms->browser_type == 'full' ? 'segments' : 'segments';

$post = $listing->post();
$post->head .= "
    <div class='ui secondary segment horizontal divider'>
    <a href='/online.users.php'>" . __('Онлайн %s', $online) . "</a>
     &#183; 
    <a href='/online.guest.php'>" . __('Гостей %s', $guest) . "</a>
    </div>";


$q = $db->query("SELECT `users_online`.* , `browsers`.`name` AS `browser` FROM `users_online` LEFT JOIN `browsers` ON `users_online`.`id_browser` = `browsers`.`id` ORDER BY `users_online`.`time_login` DESC LIMIT $user_limit");

if ($arr = $q->fetchAll()) {
    $post = $listing->post();
    //$post->class = 'ui segment ui horizontal list';
    $post->head .= "<div class='ui segment'><div class='ui horizontal list'><div class='item'>";
    foreach ($arr AS $ank) {
        $user_on = new user($ank['id_user']);

        $post->head .= "
                    <span data-tooltip='$user_on->nick' data-position='top left'>
                        <a href='/profile.view.php?id=$user_on->id'>
                            <img src='" . $user_on->getAvatar() . "' class='ui avatar image' width='48' height='48' />
                        </a>
                    </span>
                ";
    }
    $post->head .= "</div></div></div>";
}

$listing->display();

