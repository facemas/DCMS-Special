<?php

/**
 * Парсинг SQL
 */
abstract class sql_parser {

    /**
     * Выполняет запросы из SQL файла
     * @param string $file путь к sql файлу
     * @return int кол-во выполненых запросов
     */
    static public function executeQueriesFromFile($file) {
        $queries = self::getQueriesFromFile($file);
        $count = 0;
        foreach ($queries as $sql) {
            // некоторые запросы могут выполняться долго, поэтому увеличиваем временной лимит.
            if (function_exists('set_time_limit'))
                set_time_limit(600);
            // подсчитываем только успешно выполненные запросы
            if (DB::me()->query($sql))
                $count++;
        }
        return $count;
    }

    /**
     * Получение списка запросов из SQL файла
     * @param string $file путь к sql файлу
     * @return array
     */
    static public function getQueriesFromFile($file) {
        return self::getQueries(file_get_contents($file));
    }

    /**
     * Получение списка запросов из SQL строки
     * @param string $sql SQL запросы
     * @return array
     */
    static public function getQueries($sql) {
        $queries = array();
        $strlen = strlen($sql);
        $position = 0;
        $query = '';
        for (; $position < $strlen; ++$position) {
            $char = $sql {
                    $position };
            switch ($char) {
                case '-':
                    if (substr($sql, $position, 3) !== '-- ') {
                        $query .= $char;
                        break;
                    }
                case '#':
                    while ($char !== "\r" && $char !== "\n" && $position < $strlen - 1)
                        $char = $sql {
                                ++$position };
                    break;
                case '`':
                case '\'':
                case '"':
                    $quote = $char;
                    $query .= $quote;
                    while ($position < $strlen - 1) {
                        $char = $sql {
                                ++$position };
                        if ($char === '\\') {
                            $query .= $char;
                            if ($position < $strlen - 1) {
                                $char = $sql {
                                        ++$position };
                                $query .= $char;
                                if ($position < $strlen - 1)
                                    $char = $sql {
                                            ++$position };
                            } else {
                                break;
                            }
                        }
                        if ($char === $quote)
                            break;
                        $query .= $char;
                    }
                    $query .= $quote;
                    break;
                case ';':
                    $query = trim($query);
                    if ($query)
                        $queries[] = $query;
                    $query = '';
                    break;
                default:
                    $query .= $char;
                    break;
            }
        }
        $query = trim($query);
        if ($query)
            $queries[] = $query;
        return $queries;
    }

}