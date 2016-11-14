<?php

/**
 * Работа со структурами таблиц.
 * Используется для выявления изменений в структурах таблиц при обновлении движка и формирования соответствующих SQL запросов.
 */
class table_structure
{

    protected $_structure = array(); // поля и индексы таблицы
    protected $_properties = array();
    protected $db;

    /**
     * загрузка структуры из INI файла
     * @param bool|string $path Путь к файлу со структурой таблицы
     */
    function __construct($path = false)
    {
        $this->db = DB::me();
        if ($path) {
            $this->loadFromIniFile($path);
        }
    }

    /**
     * Очистка данных о структуре загруженной таблицы
     */
    public function clear()
    {
        $this->_structure = array();
        $this->_properties = array();
    }

    /**
     * загрузка структуры из INI файла
     * @param string $path Путь к файлу со структурой таблицы
     */
    function loadFromIniFile($path)
    {
        $this->clear();
        $ini = ini::read($path, 1);
        foreach ($ini as $key => $value) {
            if ($key == '~TABLE~PROPERTIES~') {
                $this->_properties = $value;
            } else {
                $this->_structure[$key] = $value;
            }
        }
    }

    /**
     * получение структуры таблицы из подключенной базы
     * @param string $table Имя таблицы
     */
    function loadFromBase($table)
    {
        $this->clear();
        // получение полей таблицы
        $q = $this->db->query("SHOW FULL COLUMNS FROM `$table`");
        if ($q) {
            while ($result = $q->fetch()) {
                $structure = array();
                $structure['type'] = $result['Type'];
                if ($result['Default'] == '' && $result['Null'] == 'YES') {
                    $structure['default_and_null'] = 'DEFAULT NULL';
                } elseif ($result['Default'] != '' && $result['Null'] == 'YES') {
                    $structure['default_and_null'] = "NULL DEFAULT '" . $result['Default'] . "'";
                } elseif ($result['Default'] != '' && $result['Null'] == 'NO') {
                    $structure['default_and_null'] = "NOT NULL DEFAULT '" . $result['Default'] . "'";
                } else {
                    $structure['default_and_null'] = 'NOT NULL';
                }
                $structure['ai'] = $result['Extra'] == 'auto_increment' ? 'AUTO_INCREMENT' : '';
                $structure['comment'] = $result['Comment'] ? "COMMENT '" . $result['Comment'] . "'" : '';
                $this->_structure['`' . $result['Field'] . '`'] = $structure;
            }
        }
        // получение ключей таблицы
        $q = $this->db->query("SHOW KEYS FROM `$table`");
        if ($q) {
            while ($result = $q->fetch()) {
                $structure = array();
                if ($result['Key_name'] == 'PRIMARY') {
                    $structure['type'] = 'PRIMARY KEY';
                } elseif ($result['Index_type'] == 'FULLTEXT') {
                    $structure['type'] = 'FULLTEXT KEY `' . $result['Key_name'] . '`';
                } elseif (!$result['Non_unique']) {
                    $structure['type'] = 'UNIQUE KEY `' . $result['Key_name'] . '`';
                } else {
                    $structure['type'] = 'KEY `' . $result['Key_name'] . '`';
                }

                if (isset($this->_structure[$structure['type']]['fields'])) {
                    $this->_structure[$structure['type']]['fields'] .= ", `" . $result['Column_name'] . "`";
                } else {
                    $this->_structure[$structure['type']]['fields'] = "`" . $result['Column_name'] . "`";
                }
            }
        }
        // получение свойств таблицы
        $q = $this->db->query("SHOW TABLE STATUS LIKE '$table'");
        if ($q){
            $properties = $q->fetch();
            $this->_properties['name'] = $properties['Name'];
            $this->_properties['engine'] = 'ENGINE=' . $properties['Engine'];
            $this->_properties['auto_increment'] = 'AUTO_INCREMENT=' . $properties['Auto_increment'];
            $this->_properties['comment'] = "COMMENT=" . $this->db->quote($properties['Comment']) . "";
        }
    }

    /**
     * сохранение структуры в INI файл
     * @param string $path
     * @return boolean
     */
    function saveToIniFile($path)
    {
        return ini::save($path, array_merge($this->_structure, array('~TABLE~PROPERTIES~' => $this->_properties)),
            true);
    }

    /**
     * получение SQL запроса на создание таблицы
     * @return string SQL запрос на создание таблицы
     */
    function getSQLQueryCreate()
    {
        $sql = "CREATE TABLE `" . $this->_properties['name'] . "` (\r\n";
        $structure = array();
        foreach ($this->_structure as $name => $struct) {
            $struct_tmp = array();
            $struct_tmp[] = $name;

            if (isset($struct['fields'])) {
                $struct_tmp[] = '(' . $struct['fields'] . ')';
            } else {
                if ($struct['type']) {
                    $struct_tmp[] = $struct['type'];
                }
                if ($struct['default_and_null']) {
                    $struct_tmp[] = $struct['default_and_null'];
                }
                if ($struct['ai']) {
                    $struct_tmp[] = $struct['ai'];
                }
                if ($struct['comment']) {
                    $struct_tmp[] = $struct['comment'];
                }
            }
            $structure[] = implode(' ', $struct_tmp);
        }

        $sql .= implode(",\r\n", $structure);

        $sql .= "\r\n) ";
        $prop_tmp = array();
        if ($this->_properties['engine']) {
            $prop_tmp[] = $this->_properties['engine'];
        }
        $prop_tmp[] = 'DEFAULT CHARSET=utf8';
        // $prop_tmp[] = $this -> _properties['auto_increment'];
        if ($this->_properties['comment']) {
            $prop_tmp[] = $this->_properties['comment'];
        }

        $sql .= implode(' ', $prop_tmp);
        return $sql;
    }

    /**
     * получение SQL запроса на изменение таблицы
     * @param \table_structure $tStruct_obj Структура целевой таблицы
     * @return string SQL запрос для приведения структуры таблицы к $tStruct_obj
     */
    function getSQLQueryChange($tStruct_obj)
    {
        $to_add = array(); // добавляем поля (индексы)
        $to_delete = array(); // удаляем поля (индексы)
        $to_edit = array(); // изменяем поля (индексы)

        foreach ($tStruct_obj->_structure as $key => $value) {
            if (!isset($this->_structure[$key])) { // если в старой таблице такого поля не существует, то добавляе в создаваемые
                $to_add[$key] = $value;
                continue;
            }
        }

        foreach ($this->_structure as $key => $value) {
            if (!isset($tStruct_obj->_structure[$key])) { // если в новой таблице такого поля не существует, то добавляе в удаляемые
                $to_delete[$key] = $value;
                continue;
            }
            $new_value = $tStruct_obj->_structure[$key];
            foreach ($value as $key2 => $value2) {
                if ($new_value[$key2] != $value2) {
                    // если хоть один из параметров расходится, то допавляем в изменяемые
                    $to_edit[$key] = $new_value;
                    continue(2);
                }
            }
        }
        // если нет изменений
        if (!$to_add /* && !$to_delete */ && !$to_edit) {
            return false;
        }
        $sql = "ALTER TABLE `" . $this->_properties['name'] . "` \r\n";
        $structure = array();


        /*
          // обработка удаляемых полей (индексов)
          ksort($to_delete);
          foreach ($to_delete as $name => $struct) {
          if (isset($struct['fields'])) {
          if (strpos($name, 'PRIMARY') === 0)
          $structure[] = 'DROP PRIMARY KEY';
          elseif (preg_match('#`(.+?)`#', $name, $m))
          $structure[] = 'DROP INDEX ' . $m[1];
          continue;
          }
          $struct_tmp = array();
          $struct_tmp[] = 'DROP';
          $struct_tmp[] = $name;
          $structure[] = implode(' ', $struct_tmp);
          }
         */

        // обработка изменяемых полей (индексов)
        foreach ($to_edit as $name => $struct) {
            $struct_tmp = array();
            $struct_tmp[] = 'CHANGE';
            $struct_tmp[] = $name;
            $struct_tmp[] = $name;
            // индексы мы менять не можем, но зато мы можем удалить и создать заново
            if (isset($struct['fields'])) {
                if (strpos($name, 'PRIMARY') === 0) {
                    $structure[] = 'DROP PRIMARY KEY';
                } elseif (preg_match('#`(.+?)`#', $name, $m)) {
                    $structure[] = 'DROP INDEX ' . $m[1];
                }
                $structure[] = 'ADD ' . $name . ' (' . $struct['fields'] . ')';
                continue;
            }
            if ($struct['type']) {
                $struct_tmp[] = $struct['type'];
            }
            if ($struct['default_and_null']) {
                $struct_tmp[] = $struct['default_and_null'];
            }
            if ($struct['ai']) {
                $struct_tmp[] = $struct['ai'];
            }
            if ($struct['comment']) {
                $struct_tmp[] = $struct['comment'];
            }
            $structure[] = implode(' ', $struct_tmp);
        }
        // обработка добавляемых полей (индексов)
        foreach ($to_add as $name => $struct) {
            $struct_tmp = array();
            $struct_tmp[] = 'ADD';
            $struct_tmp[] = $name;

            if (isset($struct['fields'])) {
                $struct_tmp[] = '(' . $struct['fields'] . ')';
            } else {
                if ($struct['type']) {
                    $struct_tmp[] = $struct['type'];
                }
                if ($struct['default_and_null']) {
                    $struct_tmp[] = $struct['default_and_null'];
                }
                if ($struct['ai']) {
                    $struct_tmp[] = $struct['ai'];
                }
                if ($struct['comment']) {
                    $struct_tmp[] = $struct['comment'];
                }
            }
            $structure[] = implode(' ', $struct_tmp);
        }

        $sql .= implode(",\r\n", $structure);

        return $sql;
    }
}
