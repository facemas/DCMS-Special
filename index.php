<?php

include_once 'sys/inc/start.php';
$doc = new document ();
$doc->title = __($dcms->title); // локализированое название сайта
$doc->head = 'home';
$widgets = (array) ini::read(H . '/sys/ini/widgets.ini'); // получаем список виджетов

\sys\dcms\EventProvider::make()->registerEvent('test.event',[
    'type'  =>  'newsview',
    'time'  =>  time()
]);
foreach ($widgets as $widget_name => $show) {
    if (!$show) {
        continue; // если стоит отметка о скрытии, то пропускаем
    }
    $widget = new widget(H . '/sys/widgets/' . $widget_name); // открываем
    $widget->display(); // отображаем
}



