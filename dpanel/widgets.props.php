<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(5);
$doc->title = __('Виджеты');
$doc->ret(__('Управление'), '/dpanel/');

$types = array('light', 'mobile', 'full');

if (isset($_POST ['save'])) {
    foreach ($types AS $type) {
        $prop_name = "widget_items_count_" . $type;
        $dcms->$prop_name = min(max((int) $_POST [$prop_name], 0), 50);
    }

    if ($dcms->save_settings()) {
        $doc->msg(__('Настройки успешно сохранены'));
    } else {
        $doc->err(__('Нет прав на запись в файл настроек'));
    }
}

$form = new form('?' . passgen());
foreach ($types AS $type) {
    $prop_name = "widget_items_count_" . $type;
    $form->text($prop_name, __('Макс. кол-во пунктов в виджете') . ' [0-50] (' . strtoupper($type) . ')', $dcms->$prop_name);
}
$form->button(__('Сохранить'), 'save');
$form->display();
