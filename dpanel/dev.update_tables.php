<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(6);
$doc->title = __('Обновление структуры таблиц');

$tables_exists = new tables();
$table_files = (array) glob(H . '/sys/preinstall/base.create.*.ini');
$tables = array();
foreach ($table_files as $table_file) {
    preg_match('#base.create\.(.+)\.ini#ui', $table_file, $m);
    $tables[] = $m[1];
}

if (!empty($_POST)) {
    foreach ($_POST as $table => $val) {
        // echo $table."<br />";
        if (!$val)
            continue;
        if (in_array($table, $tables)) {
            if (function_exists('set_time_limit'))
                set_time_limit(600);

            if (!empty($_POST['load'])) {
                if (!is_file(H . '/sys/preinstall/base.create.' . $table . '.ini'))
                    continue;

                $tab = new table_structure(H . '/sys/preinstall/base.create.' . $table . '.ini');
                // если такая таблица уже существует, то получаем запрос на ее изменение
                if (in_array($table, $tables_exists->tables)) {
                    $tab_old = new table_structure();
                    $tab_old->loadFromBase($table);
                    $sql = $tab_old->getSQLQueryChange($tab);
                } else
                    $sql = $tab->getSQLQueryCreate();

                if ($db->query($sql)) {
                    $doc->msg(__('Запрос на изменение таблицы "%s" успешно выполнен', $table));
                    $tables_exists = new tables();
                    if (in_array($table, $tables_exists->tables))
                        $doc->msg(__('Таблица "%s" успешно изменена', $table));
                    else
                        $doc->err(__('Таблица "%s" не создана', $table));
                }
            }
        }
    }
}

$listing = new listing();

foreach ($tables as $table) {

    $checked = false;
    $sql = false;
    if (!in_array($table, $tables_exists->tables)) {
        // таблица не существует в базе, значит нужно создать
        $checked = true;
    } else {
        $tab_old = new table_structure();
        $tab_old->loadFromBase($table);

        $tab_new = new table_structure(H . '/sys/preinstall/base.create.' . $table . '.ini');
        // если есть изменения, то обновляем
        if ($sql = $tab_old->getSQLQueryChange($tab_new))
            $checked = true;
    }

    $post = empty($sql) ? '' : '<pre>' . text::toOutput($sql) . '</pre>';
    if ($post) {
        $ch = $listing->checkbox();
        $ch->checked = $checked;
        $ch->name = $table;
        $ch->title = $table;
        $ch->content = $post;
    }
}

if ($listing->count()) {
    $form = new form('?' . passgen());
    $form->html($listing->fetch());
    $form->bbcode('[notice] ' . __('Структура таблиц базы данных будет изменена.'));
    $form->button(__('Выполнить запросы'), 'load');
    $form->display();
} else {
    $listing->display(__('Все таблицы находятся в актуальном состоянии'));
}

$doc->ret(__('Управление'), './');
