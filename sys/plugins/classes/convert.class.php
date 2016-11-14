<?php

/**
 * Простой конвертер UTF-8 <-> cp1251
 */
abstract class convert {

    /**
     * конвертируем из UTF в нужную кодировку (по-умолчанию Windows 1251)
     * @param string $str Строка
     * @param string $to Целевая кодировка
     * @return string
     */
    static function of_utf8($str, $to = 'cp1251') {
        if (self::charset($str) == 'UTF-8') {
            if (function_exists('mb_substr')) {
                return mb_convert_encoding($str, $to, 'UTF-8');
            }
            if (function_exists('iconv')) {
                return iconv('UTF-8', $to, $str);
            }
        }
        return $str;
    }

    /**
     * конвертируем в UTF из заданной кодировки (по-умолчанию Windows 1251)
     * @param string $str строка
     * @param string $from исходная кодировка
     * @return string
     */
    static function to_utf8($str, $from = 'cp1251') {
        if (self::charset($str) == $from) {
            if (function_exists('mb_substr')) {
                return mb_convert_encoding($str, 'UTF-8', $from);
            }
            if (function_exists('iconv')) {
                return iconv($from, 'UTF-8', $str);
            }
        }
        return $str;
    }

    /**
     * Определение UTF-8 кодировки
     * @param string $str
     * @return string кодировка (UTF-8 или cp1251)
     */
    static function charset($str) {
        if (preg_match('#[[:alpha:]]+#ui', $str)) {
            return 'UTF-8';
        } else {
            return 'cp1251';
        }
    }

}
