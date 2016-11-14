<?php

/**
 * Возвращает массив с количеством [новых, если указано время] сообщений в каждой теме
 * @param array $themes_ids Массив идентификаторов тем
 * @param int $time_from Время, с которого считать сообщения
 * @param int $group Группа пользователей
 * @return array
 * @throws Exception
 * @throws ExceptionPdoNotExists
 */
function forum_getMessagesCounters($themes_ids = array(), $time_from = 0, $group = 0) {
    $counters = array();
    $sql_prep = db::me()->prepare('SELECT COUNT(*) AS `count`, `id_theme` FROM forum_messages WHERE group_show < :g AND id_theme IN (' . join(',', array_map('intval', $themes_ids)) . ') AND `time` > :t GROUP BY id_theme');
    $sql_prep->execute(array(':t' => $time_from, ':g' => $group));

    while ($res = $sql_prep->fetch()) {
        $counters[$res['id_theme']] = $res['count'];
    }

    return $counters;
}

/**
 * Возвращает массив с количеством просмотров каждой теме
 * @param array $themes_ids массив идентификаторов тем
 * @return array
 * @throws Exception
 * @throws ExceptionPdoNotExists
 */
function forum_getViewsCounters($themes_ids = array()) {
    $counters = array();

    $sql_prep = db::me()->prepare('SELECT COUNT(*) AS `count`, `id_theme` FROM forum_views WHERE id_theme IN (' . join(',', array_map('intval', $themes_ids)) . ') GROUP BY id_theme');
    $sql_prep->execute();

    while ($res = $sql_prep->fetch()) {
        $counters[$res['id_theme']] = $res['count'];
    }

    foreach ($themes_ids as $id) {
        if (!array_key_exists($id, $counters)) {
            $counters[$id] = 0;
        }
    }

    return $counters;
}

/**
 * Возвращает массив последних просмотров тем для указанного пользователя
 * @param array $themes_ids
 * @param int $id_user идентификатор пользователя
 * @return array
 * @throws Exception
 * @throws ExceptionPdoNotExists
 */
function forum_getLastViewsTimes($themes_ids = array(), $id_user = 0) {
    $counters = array();

    $sql_prep = db::me()->prepare('SELECT MAX(`time`) AS `last_view_time`, `id_theme` FROM forum_views WHERE id_theme IN (' . join(',', array_map('intval', $themes_ids)) . ') AND `id_user` = :uid GROUP BY id_theme');
    $sql_prep->execute(array(':uid' => $id_user));

    while ($res = $sql_prep->fetch()) {
        $counters[$res['id_theme']] = $res['last_view_time'];
    }

    return $counters;
}
