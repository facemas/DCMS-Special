<?php

/**
 * класс позволяет читать/сохранять массивы из/в INI файл(а)
 * (array) = ini::read(string file [, bool is_sectionize])
 * (bool) = ini::save(string file, array to_save[, bool is_sectionize])
 */
abstract class ini {

    static function value_encode($str) {
        $str = str_replace(array("\r", "\n", "\t"), array('\r', '\n', '\t'), $str);
        return htmlentities($str, ENT_QUOTES, 'UTF-8');
    }

    static function value_decode($str) {
        $str = str_replace(array('\r', '\n', '\t'), array("\r", "\n", "\t"), $str);
        return html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    }

    /**
     * открытие INI файла из строки
     * @param string $string
     * @param boolean $sect
     * @return array
     */
    static function openString($string, $sect = false) {
        $tmp_file = TMP . '/' . passgen() . '.tmp';
        if (!@file_put_contents($tmp_file, $string)) {
            return false;
        }
        $ini = self::read($tmp_file, $sect);
        unlink($tmp_file);
        return $ini;
    }

    /**
     * Чтение INI файла
     * @param string $file Путь к файлу в ФС
     * @param boolean $sect
     * @return array
     */
    static function read($file, $sect = false) {
        $arr = @parse_ini_file($file, $sect);
        if ($arr) {
            if ($sect) {
                foreach ($arr as $key => $value) {
                    foreach ($value as $key2 => $value2) {
                        $arr [$key] [$key2] = self::value_decode($value2);
                    }
                }
            } else {
                foreach ($arr as $key => $value) {
                    $arr [$key] = self::value_decode($value);
                }
            }
        }
        return $arr;
    }

    /**
     * Сохраниение массива в INI файл
     * @param string $file Путь к INI файлу
     * @param array $array Сохраняемый массив
     * @param boolean $sect
     * @return boolean
     */
    static function save($file, $array, $sect = false) {
        $tmp_file = TMP . '/tmp.' . passgen() . '.ini';
        $ini = array();
        $ini[] = "; saved by ini.class.php";
        if ($sect) {
            foreach ($array as $key => $value) {
                $ini [] = '[' . self::value_encode($key) . ']';
                foreach ($value as $key2 => $value2) {
                    $ini [] = "$key2 = \"" . self::value_encode($value2) . "\";";
                }
            }
        } else {
            foreach ($array as $key => $value) {
                $ini [] = "$key = \"" . self::value_encode($value) . "\";";
            }
        }

        // сохраняем во временный файл
        if (!@file_put_contents($tmp_file, implode("\r\n", $ini))) {
            return false;
        }
        @chmod($tmp_file, filesystem::getChmodToWrite());

        if (IS_WINDOWS) {
            // в винде файл перед заменой нужно удалить
            if (@file_exists($file) && !@unlink($file)) {
                return false;
            }
        }
        // переименовываем временный файл в нужный нам
        if (!@rename($tmp_file, $file)) {
            return false;
        }
        @chmod($file, filesystem::getChmodToWrite());

        return true;
    }

}