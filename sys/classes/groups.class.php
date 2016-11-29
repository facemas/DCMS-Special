<?php

/**
 * Группы пользователей
 */
abstract class groups {

    static function load_ini() {
        static $ini;
        if (!isset($ini)) {
            $ini = ini::read(H . '/sys/ini/groups.ini', true);
        }
        return $ini;
    }

    /**
     * Название группы
     * @param int $group
     * @return string
     */
    static function name($group) {
        $ini = self::load_ini();
        if (isset($ini[$group]['name']))
            return __($ini[$group]['name']);
        return __('Ошибка группы');
    }

    /**
     * Группа создателя
     * @return int
     */
    static function max() {
        return max(array_keys(self::load_ini()));
    }

    /**
     * Возвращает массив пользователей (с указанной группы или администратора)
     * @param int|boolean $group группа
     * @return Array<\user>
     */
    static function getAdmins($group = false) {
        $users = array();
        if ($group === false) {
            $group = self::max();
        }
        $group = (int) $group;

        $q = DB::me()->prepare("SELECT `id` FROM `users` WHERE `group` >= ?");
        $q->execute(Array($group));
        if ($arr = $q->fetchAll()) {
            foreach ($arr AS $us) {
                $users[] = new user($us['id']);
            }
        }
        return $users;
    }

}
