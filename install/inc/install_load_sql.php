<?php

class install_load_sql {

    var $tables;

    function __construct() {
        db_connect();
        $this->tables = new tables();

        if (empty($_SESSION['rename_prefix']))
            $_SESSION['rename_prefix'] = '~' . TIME . '~';

        foreach ($this->tables->tables as $table) {
            if ($table {
                    0} == '~')
                continue;
            DB::me()->query("ALTER TABLE `$table` RENAME `" . $_SESSION['rename_prefix'] ."$table`");
        }
    }

    function actions() {
        $return = true;
        global $options;

        $files_ini = array();
        $files_sql = array();

        if (!empty($_POST['load_data']) && !empty($options['new_base']))
            $files_sql = (array) glob(H . '/sys/preinstall/base.data.*.sql');

        $files_ini = (array) glob(H . '/sys/preinstall/base.create.*.ini');

        foreach ($files_ini as $file) {
            $tab = new table_structure($file);
            $sql = $tab->getSQLQueryCreate();     
             //echo '<pre>'.output_text($sql).';</pre><br />'; 
            if (!DB::me()->query($sql))
                $return = false;
        }
        // exit;
        foreach ($files_sql as $file) {
            $sqls = sql_parser::getQueriesFromFile($file);
            foreach ($sqls as $sql) {
                if (!DB::me()->query($sql))
                    $return = false;
            }
        }

        $_SESSION['install_load_sql_false'] = !$return;

        return $return;
    }

    function form() {
        echo __('На данном этапе создадутся необходимые для работы движка таблицы в базе данных. Если в базе уже находятся какие-либо таблицы, к ним будет добавлен префикс с временной меткой');

        global $options;
        if (!empty($options['new_base'])) {
            $files = glob(H . '/sys/preinstall/base.data.*.sql');

            if ($files)
                echo '<br /><label><input type="checkbox" checked="checked" value="1" name="load_data" />' . __('Загрузить содержимое таблиц') . '</label>';
        }
        if (!empty($_SESSION['install_load_sql_false']))
            echo "<br />".__("При выполнении SQL запросов возникли ошибки") ;

        return true;
    }

}

?>