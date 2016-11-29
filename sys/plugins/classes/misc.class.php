<?php

/**
 * Различные полезные функции
 */
abstract class misc {

    /**
     * Удаление пользователя и всех связанных с ним данных
     * @param int $id Идентификатор пользователя
     */
    static function user_delete($id) {
        $id = (int) $id;
        $tables = ini::read(H . '/sys/ini/user.tables.ini', true);
        foreach ($tables AS $v) {
            $res = DB::me()->prepare("DELETE FROM `" . $v['table'] . "` WHERE `" . $v['row'] . "` = ?");
            $res->execute(Array($id));
        }
        $res = DB::me()->prepare("DELETE FROM `users` WHERE `id` = ?");
        $res->execute(Array($id));
    }

    static function logaut($id, $method, $status, $vk_id = 0) {
        global $dcms;

        $ua = (string) @$_SERVER['HTTP_USER_AGENT'];

        $q = DB::me()->prepare("SELECT * FROM `log_of_user_aut` WHERE `id_user` = :id AND `user_id` = :uid AND `iplong` = :ip_long AND `browser_ua` = :ua AND `domain` = :domain AND `method` = :method AND `status` = :status ORDER BY `time` DESC LIMIT 1");
        $q->execute(Array(':id' => $id, ':uid' => $vk_id, ':ip_long' => $dcms->ip_long, ':ua' => $ua, ':domain' => $dcms->subdomain_main, ':method' => $method, ':status' => $status));

        if (!$row = $q->fetch()) {
            $res = DB::me()->prepare("INSERT INTO `log_of_user_aut` (`id_user`,`user_id`,`method`,`iplong`, `time`, `id_browser`,`browser`,`browser_ua`,`domain`,`status`) VALUES (:id,:uid,:method,:ip_long,:time,:br_id,:br_name,:ua,:domain,:status)");
            $res->execute(Array(':id' => $id, ':uid' => $vk_id, ':ip_long' => $dcms->ip_long, ':ua' => $ua, ':domain' => $dcms->subdomain_main, ':method' => $method, ':status' => $status, ':br_id' => $dcms->browser_id, ':br_name' => $dcms->browser_name, ':time' => TIME));
        } else {
            $res = DB::me()->prepare("UPDATE `log_of_user_aut` SET `time` = :time, `id_browser` = :br_id, `count` = `count` + 1 WHERE `id_user` = :id AND `user_id` = :uid AND `iplong` = :ip_long AND `browser_ua` = :ua AND `domain` = :domain AND `method` = :method AND `status` = :status LIMIT 1");
            $res->execute(Array(':id' => $id, ':uid' => $vk_id, ':ip_long' => $dcms->ip_long, ':ua' => $ua, ':domain' => $dcms->subdomain_main, ':method' => $method, ':status' => $status, ':br_id' => $dcms->browser_id, ':time' => TIME));
        }
    }

    /**
     * Запись данных в системный лог
     * @param string $text Текст сообщения
     * @param string $module Модуль, к которому относится сообщение
     * @return boolean
     */
    static function log($text, $module = 'system') {
        $time = date("H:i:s d.m.Y");
        $file = H . '/sys/logs/' . basename($module) . '.log';

        if (!$fo = @fopen($file, 'a')) {
            return false;
        }
        $content = "\r\n(" . $time . ') ' . $text;
        if (!@fwrite($fo, $content)) {
            return false;
        }
        fclose($fo);
        return true;
    }

    /**
     * перемещение ключа $key массива $array на $step шагов
     * @param array $array
     * @param string $key
     * @param int $step
     * @return array
     */
    static function array_key_move(&$array, $key, $step = 1) {
        return arraypos::move($array, $key, $step);
    }

    /**
     *
     * @param int $num
     * @param string $one
     * @param string $two
     * @param string $more
     * @return string
     */
    static function number($num, $one, $two, $more) {
        $num = (int) $num;
        $l2 = substr($num, strlen($num) - 2, 2);

        if ($l2 >= 5 && $l2 <= 20)
            return $more;
        $l = substr($num, strlen($num) - 1, 1);
        switch ($l) {
            case 1:
                return $one;
                break;
            case 2:
                return $two;
                break;
            case 3:
                return $two;
                break;
            case 4:
                return $two;
                break;
            default:
                return $more;
                break;
        }
    }

    /**
     * Вычисление возраста
     * @param int $g Год
     * @param int $m Месяц
     * @param int $d День
     * @param boolean $read
     * @return string
     */
    static function get_age($g, $m, $d, $read = false) {
        if (strlen($g) == 2)
            $g += 1900;
        if (strlen($g) == 3)
            $g += 1000;
        $age = date('Y') - $g;
        if (date('n') < $m)
            $age--; // год не полный, если текущий месяц меньше
        elseif (date('n') == $m && date('j') < $d)
            $age--; // год не полный, если текущий месяц совпадает, но день меньше
        if ($read)
            return $age . ' ' . self::number($age, __('год'), __('года'), __('лет'));

        return $age;
    }

    /**
     * Читабельное представление размера информации
     * @param int $filesize размер в байтах
     * @return string размер в (KB, MB...)
     */
    static function getDataCapacity($filesize = 0) {
        $filesize_ed = __('байт');
        if ($filesize >= 1024) {
            $filesize = round($filesize / 1024, 2);
            $filesize_ed = __('Кб');
        }
        if ($filesize >= 1024) {
            $filesize = round($filesize / 1024, 2);
            $filesize_ed = __('Мб');
        }
        if ($filesize >= 1024) {
            $filesize = round($filesize / 1024, 2);
            $filesize_ed = __('Гб');
        }
        if ($filesize >= 1024) {
            $filesize = round($filesize / 1024, 2);
            $filesize_ed = __('Тб');
        }
        if ($filesize >= 1024) {
            $filesize = round($filesize / 1024, 2);
            $filesize_ed = __('Пб');
        }

        return $filesize . ' ' . $filesize_ed;
    }

    static function returnBytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            case 'g':
                $val *= 1073741824;
                break;
            case 'm':
                $val *= 1048576;
                break;
            case 'k':
                $val *= 1024;
                break;
        }

        return $val;
    }

    /**
     * читабельное представление времени с учетом часового пояса пользователя
     * @global \user $user
     * @param int $time Время в формате timestamp
     * @param boolean $adaptive Адаптивное представлени (вместо полной даты использовать "сегодня", "вчера")
     * @return string
     */
    static function when($time = null, $adaptive = true) {
        if ($time > TIME) {
            $time -= TIME;
            $mes = 0;
            $day = 0;
            $hour = 0;
            $min = 0;
            $sec = 0;
            if ($time) {
                $sec = $time % 60;
            }
            if ($time >= 60) {
                $min = floor($time / 60 % 60);
            }
            if ($time >= 3600) {
                $hour = floor($time / 3600 % 24);
            }
            if ($time >= 86400) {
                $day = floor($time / 86400 % 30);
            }
            if ($time >= 2592000) {
                $mes = floor($time / 2592000 % 12);
            }

            if ($mes) {
                return $mes . ' месяц' . self::number($mes, '', 'а', 'ев') . ($day ? (', ' . $day . ' ' . self::number($day, 'день', 'дня', 'дней') . ($hour ? ' и ' . $hour . ' час' . self::number($hour, '', 'а', 'ов') : '')) : '');
            }
            if ($day) {
                return $day . ' ' . self::number($day, 'день', 'дня', 'дней') . ($hour ? (', ' . $hour . ' час' . self::number($hour, '', 'а', 'ов') . ($min ? ' и ' . $min . ' минут' . self::number($min, 'а', 'ы', '') : '')) : '');
            }
            if ($hour) {
                return $hour . ' час' . self::number($hour, '', 'а', 'ов') . ($min ? (', ' . $min . ' минут' . self::number($min, 'а', 'ы', '') . ($sec ? ' и ' . $sec . ' секунд' . self::number($sec, 'а', 'ы', '') : '')) : '');
            }
            if ($min) {
                return $min . ' минут' . self::number($min, 'а', 'ы', '') . ($sec ? ' и ' . $sec . ' секунд' . self::number($sec, 'а', 'ы', '') : '');
            }
            return $sec . ' секунд' . self::number($sec, 'а', 'ы', '');
        } else {
            global $user;
            if (!$time) {
                $time = TIME;
            }
            if ($user->group) {
                $time_shift = $user->time_shift;
            } else {
                $time_shift = 0;
            }
            $time = $time + $time_shift * 3600;
            $vremja = date('j M Y в H:i', $time);
            $time_p[0] = date('j n Y', $time);
            $time_p[1] = date('H:i', $time);
            if ($adaptive && $time_p[0] == date('j n Y', TIME + $time_shift * 60 * 60)) {
                $vremja = date('H:i:s', $time);
            }
            if ($adaptive && $time_p[0] == date('j n Y', TIME - 60 * 60 * (24 - $time_shift))) {
                $vremja = __("Вчера в %s", $time_p[1]);
            }

            if ($adaptive && $time_p[0] == date('j n Y', TIME - 60 * 60 * (48 - $time_shift))) {
                $vremja = __("Позавчера в %s", $time_p[1]);
            }

            $vremja = str_replace('Jan', __('Янв'), $vremja);
            $vremja = str_replace('Feb', __('Фев'), $vremja);
            $vremja = str_replace('Mar', __('Марта'), $vremja);
            $vremja = str_replace('May', __('Мая'), $vremja);
            $vremja = str_replace('Apr', __('Апр'), $vremja);
            $vremja = str_replace('Jun', __('Июня'), $vremja);
            $vremja = str_replace('Jul', __('Июля'), $vremja);
            $vremja = str_replace('Aug', __('Авг'), $vremja);
            $vremja = str_replace('Sep', __('Сент'), $vremja);
            $vremja = str_replace('Oct', __('Окт'), $vremja);
            $vremja = str_replace('Nov', __('Ноября'), $vremja);
            $vremja = str_replace('Dec', __('Дек'), $vremja);
            return $vremja;
        }
    }

    # Вывод текстового времени

    static function times($time = null) {
        global $user;

        if (!$time) {
            $time = TIME;
        }

        $t = round((TIME - $time) / 60);
        if ($t < 1) {
            $t = __('только что');
        }
        if ($t >= 1 && $t < 60) {
            $t = __('%s ' . self::number($t, 'минуту', 'минуты', 'минут') . ' назад', $t);
        }
        if ($t >= 60 && $t < 1440) {
            $t = round($t / 60);
            $t = __('%s ' . self::number($t, 'час', 'часа', 'часов') . ' назад', $t);
        }
        if ($t >= 1440) {
            $t = round($t / 60 / 24);
            $t = __('%s ' . self::number($t, 'день', 'дня', 'дней') . ' назад', $t);
        }

        return $t;
    }

    # Вывод текстового укороченного времени

    static function timek($time = null) {
        global $user;

        if (!$time) {
            $time = TIME;
        }

        $t = round((TIME - $time) / 60);
        if ($t < 1) {
            $t = __('только что');
        }
        if ($t >= 1 && $t < 60) {
            $t = __('%s ' . self::number($t, 'м', 'м', 'м') . '. назад', $t);
        }
        if ($t >= 60 && $t < 1440) {
            $t = round($t / 60);
            $t = __('%s ' . self::number($t, 'ч', 'ч', 'ч') . '. назад', $t);
        }
        if ($t >= 1440) {
            $t = round($t / 60 / 24);
            $t = __('%s ' . self::number($t, 'д', 'д', 'д') . '. назад', $t);
        }

        return $t;
    }

    /**
     * Вывод названия месяца
     * @param int $num номер месяца (с 1)
     * @param int $v вариант написания
     * @return string
     */
    static function getLocaleMonth($num, $v = 1) {
        switch ($num) {
            case 1:
                return __('Январ' . ($v ? 'я' : 'ь'));
            case 2:
                return __('Феврал' . ($v ? 'я' : 'ь'));
            case 3:
                return __('Март' . ($v ? 'а' : ''));
            case 4:
                return __('Апрел' . ($v ? 'я' : 'ь'));
            case 5:
                return __('Ма' . ($v ? 'я' : 'й'));
            case 6:
                return __('Июн' . ($v ? 'я' : 'ь'));
            case 7:
                return __('Июл' . ($v ? 'я' : 'ь'));
            case 8:
                return __('Август' . ($v ? 'а' : ''));
            case 9:
                return __('Сентябр' . ($v ? 'я' : 'ь'));
            case 10:
                return __('Октябр' . ($v ? 'я' : 'ь'));
            case 11:
                return __('Ноябр' . ($v ? 'я' : 'ь'));
            case 12:
                return __('Декабр' . ($v ? 'я' : 'ь'));
            default:
                return false;
        }
    }

}
