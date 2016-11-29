<?php

/**
 * Работа со смайлами
 */
abstract class smiles {

    static function get_ini() {
        static $ini = false;
        if ($ini === false) {
            $ini = (array) ini::read(H . '/sys/ini/smiles.ini');
        }
        return $ini;
    }

    /**
     * Обработка смайлов во входящем сообщении
     * @param string $str
     * @return string
     */
    static function input($str) {
        $smiles = self::get_ini();
        $str = preg_replace('#([\.:\*])(' . implode('|', array_keys($smiles)) . ')\1#uim', '[smile]\2[/smile]', $str);
        return $str;
    }

    /**
     * Получение тега IMG со смайлом по его названию
     * @param string $smile название смайла
     * @return string
     */
    static function bbcode($smile) {
        $smiles = self::get_ini();
        if (empty($smiles[$smile])) {
            return false;
        }
        return '<img src="/sys/images/smiles/' . $smiles[$smile] . '.gif" alt="' . $smile . '" />';
    }
}