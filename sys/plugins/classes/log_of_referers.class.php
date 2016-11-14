<?php

/**
 * Запись переходов с других сайтов
 */
class log_of_referers {

    var $is_referer = false;
    private $url = array();
    private $referer = null;

    function log_of_referers() {
        // массив использованных рефереров
        if (!isset($_SESSION['LAST_REFERER']))
            $_SESSION['LAST_REFERER'] = array();
        $lr = &$_SESSION['LAST_REFERER'];
        if (!empty($_SERVER['HTTP_REFERER']) && $url = @parse_url($_SERVER['HTTP_REFERER'])) {
            if (!empty($url['host']) && $url['host'] != $_SERVER['HTTP_HOST'] && !in_array($url['host'], $lr)) {
                // защита от накрутки (запись происходит только при следующем обращении)
                $_SESSION['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
                return true;
            }
        }
        if (!empty($_SESSION['HTTP_REFERER']) && $url = @parse_url($_SESSION['HTTP_REFERER'])) {
            $this->referer = $_SESSION['HTTP_REFERER'];
            // уже использованые рефереры
            $lr[] = $url['host'];
            unset($_SESSION['HTTP_REFERER']);
            $this->url = $url;
            $site = $this->id_site();
            $this->add_to_log($site);
        }
    }

    private function id_site() {
        $q = DB::me()->prepare("SELECT id FROM `log_of_referers_sites` WHERE `domain` = ? LIMIT 1");
        $q->execute(Array($this->url['host']));
        if (!$row = $q->fetch()) {
            $res = DB::me()->prepare("INSERT INTO `log_of_referers_sites` (`domain`, `time`) VALUES (?, ?)");
            $res->execute(Array($this->url['host'], TIME));
            return DB::me()->lastInsertId();
        }
        $res = DB::me()->prepare("UPDATE `log_of_referers_sites` SET `time` = ?, `count` = `count` + 1 WHERE `id` = ? LIMIT 1");
        $res->execute(Array(TIME, $row['id']));
        return $row['id'];
    }

    private function add_to_log($id) {
        $res = DB::me()->prepare("INSERT INTO `log_of_referers` (`id_site`, `time`, `full_url`) VALUES (?, ?, ?)");
        $res->execute(Array($id, TIME, $this->referer));
    }

}
