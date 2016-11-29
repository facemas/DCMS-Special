<?php

/**
 * Работа с данными произвольных пользователей
 * Class api_users
 */
class api_users implements api_controller {

    /**
     * Получение данных произвольного пользователя
     * @param $id int Идентификатор пользователя
     * @return array
     */
    public static function getData($id) {
        $user = new user((int) $id);
        return $user->getCustomData(array('login', 'group', 'last_visit', 'sex', 'balls'));
    }

}
