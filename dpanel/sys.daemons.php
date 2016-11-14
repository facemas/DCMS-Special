<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(5);
$doc->title = __('Системные службы');

if (isset($_POST['save'])) {
    $dcms->log_of_visits = (int) !empty($_POST['log_of_visits']);
    $dcms->log_of_referers = (int) !empty($_POST['log_of_referers']);
    $dcms->clear_tmp_dir = (int) !empty($_POST['clear_tmp_dir']);
    $dcms->update_auto = min(max($_POST ['update_auto'], 0), 2);
    $dcms->update_auto_time = (int) $_POST['update_auto_time'];
    $dcms->save_settings($doc);
}


$form = new form('?' . passgen());
$form->checkbox('log_of_visits', __('Журнал посещений'), $dcms->log_of_visits);
$form->checkbox('log_of_referers', __('Журнал рефереров'), $dcms->log_of_referers);
$form->checkbox('clear_tmp_dir', __('Чистка папки с временными файлами'), $dcms->clear_tmp_dir);

$options = array();
$options[] = array('3600', __('Раз в час'), $dcms->update_auto_time == '3600');
$options[] = array('21600', __('Раз в 6 часов'), $dcms->update_auto_time == '21600');
$options[] = array('43200', __('Раз в 12 часов'), $dcms->update_auto_time == '43200');
$options[] = array('86400', __('Раз в сутки'), $dcms->update_auto_time == '86400');

$form->select('update_auto_time', __('Периодичность проверки новой версии'), $options);

$options = array();
$options[] = array('0', __('Отключено'), $dcms->update_auto == '0');
$options[] = array('1', __('Уведомлять о новой версии'), $dcms->update_auto == '1');
$options[] = array('2', __('Устанавливать новую версию'), $dcms->update_auto == '2');
$form->select('update_auto', __('Автоматическое обновление'), $options);

$form->button(__('Сохранить'), 'save');
$form->display();

$doc->ret(__('Управление'), '/dpanel/');
