<?php

/**
 * Класс для работы с сообщеними пользователя
 * Class api_notification
 */
class api_notification implements api_controller {

    public static function get($request_data) {
        $user = current_user::getInstance();
        if (!$user->id)
            throw new ApiAuthRequiredException($request_data);

        $ank = new user((int) @$request_data['id_user']);
        if (!$ank->id)
            throw new ApiException($request_data, __("Не указан контакт (id_user)"));

        // только непрочитанные
        $only_unreaded = !empty($request_data['only_unreaded']);
        // отмечать все письма как прочитанные
        $set_readed = !empty($request_data['set_readed']);
        // с выбранного времени
        $time_from = (int) @$request_data['time_from'];

        // начало списка
        $offset = !isset($request_data['offset']) ? 0 : (int) @$request_data['offset'];
        // кол-во писем
        $count = !isset($request_data['count']) ? 30 : (int) @$request_data['count'];

        if ($set_readed) {
            // отмечаем письма от этого человека как прочитанные
            $res = db::me()->prepare("UPDATE `notification` SET `is_read` = '1' WHERE `id_user` = ? AND `id_sender` = ?");
            $res->execute(Array($user->id, $ank->id));
        }

        $notification = array();
        $q = db::me()->prepare("SELECT * FROM `notification` WHERE `time` > :time_from " . ($only_unreaded ? ' AND `is_read` = 0' : '') . " AND ((`id_user` = :id_user AND `id_sender` = :id_ank) OR (`id_user` = :id_ank AND `id_sender` = :id_user)) ORDER BY `id` DESC LIMIT $offset, $count");
        $q->execute(Array(
            ':time_from' => $time_from,
            ':id_user' => $user->id,
            ':id_ank' => $ank->id
        ));
        while ($m = $q->fetch()) {
            $notification[] = array('id' => (int) $m['id'],
                'id_sender' => (int) $m['id_sender'],
                'mess' => text::toOutput($m['mess']),
                'time' => (int) $m['time'],
                'is_read' => (bool) $m['is_read']
            );
        }
        return $notification;
    }

}
