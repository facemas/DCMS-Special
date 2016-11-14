<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(groups::max());
$doc->title = __('Сохранение таблиц');

$tables = new tables();

if (!empty($_POST)) {
    foreach ($_POST as $table => $val) {
        // echo $table."<br />";
        if (!$val)
            continue;
        if (in_array($table, $tables->tables)) {
            if (function_exists('set_time_limit'))
                set_time_limit(600);

            if (!empty($_POST['create'])) {
                $tab = new table_structure();
                $tab->loadFromBase($table);
                $tab->saveToIniFile(H . '/sys/preinstall/base.create.' . $table . '.ini');
            }
            if (!empty($_POST['data'])) {
                $tables->save_data(H . '/sys/preinstall/base.data.' . $table . '.sql', $table);
            }
        }
    }

    if (!empty($_POST['create'])) {
        $doc->msg(__("Структура таблиц успешно сохранена"));
    }
    if (!empty($_POST['data'])) {
        $doc->msg(__("Содержимое таблиц успешно сохранено"));
    }

    if (@copy(H . '/sys/ini/settings.ini', H . '/sys/preinstall/settings.ini')) {
        $doc->msg(__("Предустановочные параметры успешно сохранены"));
    }
}

$listing = new listing();
foreach ($tables->tables as $table) {
    if ($table {0} == '~') {
        continue;
    }
    $ch = $listing->checkbox();
    $ch->name = $table;
    $ch->title = $table;
    $ch->checked = true;
}

$form = new form('?' . passgen());
$form->html($listing->fetch());
$form->bbcode('[notice] ' . __('Структура и данные таблиц сохранятся в папке sys/preinstall и в дальнейшем могут быть использованы для установки движка с существующими данными'));
$form->button(__('Структура'), 'create', false);
$form->button(__('Данные'), 'data', false);
$form->display();

$doc->ret(__('Управление'), './');
