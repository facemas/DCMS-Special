<?php

include_once '../sys/inc/start.php';
$doc = new document(2);
$doc->title = __('Панель управления');
$menu = new menu_ini('dpanel'); // загружаем меню dPanel
$menu->display(); // выводим меню dPanel