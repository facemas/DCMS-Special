<?php
if (current_user::getInstance()->group){
    $menu = new menu_ini('user');
    $menu->display();
}
