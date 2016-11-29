<?php

/**
 * Класс для работы со списком друзей
 * Class api_friends
 */
abstract class api_friends implements api_controller {

    /**
     * получение списка друзей
     * @param mixed $request_data
     * @throws ApiAuthRequiredException
     * @return mixed
     */
    public static function get($request_data) {
        $user = current_user::getInstance();
        if (!$user->id)
            throw new ApiAuthRequiredException($request_data);

        $q = db::me()->prepare("SELECT * FROM `friends` WHERE `id_user` = ? ORDER BY `confirm` ASC, `time` DESC");
        $q->execute(Array($user->id));
        return $q->fetchAll();
    }

}
