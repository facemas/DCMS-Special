<?php

/**
 * Работа с таблицами в базе
 */
class tables
{

    public $tables = array();
    private $db;

    function __construct()
    {
        $this->db = DB::me();
        $tab = $this->db->query('SHOW TABLES');
        while ($table = $tab->fetch(PDO::FETCH_BOTH)) {
            $this->tables[] = $table[0];
        }
    }

    /**
     * получение sql запроса на создание таблицы
     * @param string $table Имя таблицы
     * @param boolean $auto_increment включать в запрос значение auto increment
     * @return string
     */
    function get_create($table, $auto_increment = true)
    {
        $sql = "/* Структура таблицы `$table` */\r\n";
        $row = $this->db->query("SHOW CREATE TABLE " . $this->db->quote($table) . "")->fetch();
        if (!$auto_increment) {
            $row[1] = preg_replace('#AUTO_INCREMENT\=[0-9]+#ui', '/*\0*/', $row[1]);
        }
        return $sql . $row[1];
    }

    /**
     * Получение дампа таблицы для вставки
     * @param string $table Имя таблицы
     * @param int $c_ins Максимальное кол-во строк в одном INSERT`е
     * @return string
     */
    function get_data($table, $c_ins = 2000)
    {
        $sql = '';
        $res = $this->db->query("SELECT COUNT(*) FROM " . $this->db->quote($table) . "");
        $num_row_all = $res->fetchColumn();
        $start = 0;

        if ($num_row_all) {
            $sql .= "/* Данные таблицы `$table` */\r\n";
            $res = $this->db->query("SELECT * FROM " . $this->db->quote($table) . " LIMIT 1");
            $table_keys = @implode("`, `", @array_keys($res->fetch()));
            while ($start < $num_row_all) {
                $res = $this->db->query("SELECT * FROM `$table` LIMIT " . $start . ", " . $c_ins);
                $res_cnt = $this->db->query("SELECT COUNT(*) FROM `$table` LIMIT " . $start . ", " . $c_ins);
                if ($num_row_all > $c_ins)
                    $sql .= "/* блок записей $start - " . ($start + $c_ins) . " */\r\n";

                $sql .= "INSERT INTO " . $this->db->quote($table) . " (`$table_keys`) VALUES \r\n";
                $num_row = $res_cnt->fetchColumn();
                $counter = 0;
                while (($row = $res->fetch())) {
                    $values = @array_values($row);

                    foreach ($values as $k => $v) {
                        $values[$k] = $this->db->quote(preg_replace("#(\n|\r)+#", '\n', $v));
                    }
                    $values_string = @implode(', ', $values);
                    $counter++;
                    $sql .= "($values_string)" . ($counter == $num_row ? ";\r\n" : ", \r\n");
                }
                $start = $start + $c_ins;
            }
        } else
            $sql .= "/* Таблица `$table` пуста */\r\n";

        return $sql;
    }

    /**
     * Сохранение запроса на создание таблицы в файл
     * @param string $path путь к сохраняемому файлу
     * @param string $table имя таблицы
     * @param boolean $ai auto_increment
     * @return boolean
     */
    function save_create($path, $table, $ai = false)
    {
        return @file_put_contents($path, $this->get_create($table, $ai));
    }

    /**
     * Сохранение дампа таблицы в файл
     * @param string $path путь к сохраняемому файлу
     * @param string $table имя таблицы
     * @param int $c_ins Максимальное кол-во строк в одном INSERT`е
     * @return boolean
     */
    function save_data($path, $table, $c_ins = 2000)
    {
        return @file_put_contents($path, $this->get_data($table, $c_ins));
    }

}