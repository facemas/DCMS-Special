<?php

include_once 'sys/inc/start.php';
$doc = new document ();
$doc->title = __($dcms->title); // локализированое название сайта
$doc->head = 'home';
$widgets = (array) ini::read(H . '/sys/ini/widgets.ini'); // получаем список виджетов

foreach ($widgets as $widget_name => $show) {
    if (!$show) {
        continue; // если стоит отметка о скрытии, то пропускаем
    }
    $widget = new widget(H . '/sys/widgets/' . $widget_name); // открываем
    $widget->display(); // отображаем
}

$event_provider = new sys\dcms\EventProvider();
$event_provider->push()
$event_provider->run();

$event_provider = new sys\dcms\EventProvider();