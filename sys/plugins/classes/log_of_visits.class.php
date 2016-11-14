<?php

/**
 * Запись посещений
 */
class log_of_visits {

    private $db;

    function __construct() {
        global $dcms;
        $this->db = DB::me();
        if (!cache_log_of_visits::get($dcms->ip_long)) {
            $res = $this->db->prepare("INSERT INTO `log_of_visits_today` (`time`, `browser_type`, `id_browser`, `iplong`) VALUES (?, ?, ?, ?)");
            $res->execute(Array(DAY_TIME, browser::getIsRobot() ? 'robot' : browser::getType(), $dcms->browser_id, $dcms->ip_long));
            cache_log_of_visits::set($dcms->ip_long, true, 1);
        }
    }

    // подведение итогов посещений по дням
    function tally() {
        //   $res = $this->db->query("LOCK TABLES `log_of_visits_today` WRITE READ, `log_of_visits_for_days` WRITE READ");
        // запрашиваем дни, которые есть в базе исключая текущий
        $q = $this->db->prepare("SELECT DISTINCT `time`  FROM `log_of_visits_today` WHERE `time` <> ?");
        $q->execute(Array(DAY_TIME));
        $res_hits = $this->db->prepare("SELECT COUNT(*) FROM `log_of_visits_today` WHERE `time` = ? AND `browser_type` = ?");
        $res_hosts = $this->db->prepare("SELECT COUNT(DISTINCT `iplong` , `id_browser`) FROM `log_of_visits_today` WHERE `time` = ? AND `browser_type` = ?");
        $res_insert = $this->db->prepare("INSERT INTO `log_of_visits_for_days` (`time_day`, `hits_full`,`hosts_full`,`hits_light`,`hosts_light`,`hits_mobile`,`hosts_mobile`,`hits_robot`,`hosts_robot`) VALUES (?,?,?,?,?,?,?,?,?)");
        while ($day = $q->fetch()) {
            $res_hits->execute(Array($day['time'], 'light'));
            $hits['light'] = $res_hits->fetchColumn();
            $res_hits->execute(Array($day['time'], 'mobile'));
            $hits['mobile'] = $res_hits->fetchColumn();
            $res_hits->execute(Array($day['time'], 'full'));
            $hits['full'] = $res_hits->fetchColumn();
            $res_hits->execute(Array($day['time'], 'robot'));
            $hits['robot'] = $res_hits->fetchColumn();
            $res_hosts->execute(Array($day['time'], 'light'));
            $hosts['light'] = $res_hosts->fetchColumn();
            $res_hosts->execute(Array($day['time'], 'mobile'));
            $hosts['mobile'] = $res_hosts->fetchColumn();
            $res_hosts->execute(Array($day['time'], 'full'));
            $hosts['full'] = $res_hosts->fetchColumn();
            $res_hosts->execute(Array($day['time'], 'robot'));
            $hosts['robot'] = $res_hosts->fetchColumn();

            $res_insert->execute(Array($day['time'], $hits['full'], $hosts['full'], $hits['light'], $hosts['light'], $hits['mobile'], $hosts['mobile'], $hits['robot'], $hosts['robot']));
        }
        $res = $this->db->prepare("DELETE FROM `log_of_visits_today` WHERE `time` <> ?");
        $res->execute(Array(DAY_TIME));
        // оптимизация таблиц после удаления данных
        $this->db->query("OPTIMIZE TABLE `log_of_visits_today`");
        // разблокируем таблицы
        $this->db->query("UNLOCK TABLES");
    }

}
