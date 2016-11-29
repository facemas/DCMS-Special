<?php

/**
 * Список возможных нарушений
 * TODO: переписать. Список нарушений будет динамически добавляться. Хранение перенести в базу.
 */
class menu_code extends menu_ini {

    function __construct($menu_name) {
        parent::__construct($menu_name);
    }

    function options($selected = false) {
        $options = array();
        foreach ($this->menu_arr as $key => $value) {
            if (!empty($value ['razdel'])) {
                continue;
            }
            $options [] = array($key, $key, $selected && $selected == $key);
        }
        return $options;
    }

    private function mn($str) {
        switch ($str) {
            case 'мн' :
                return 60;
            case 'мин' :
                return 60;
            case 'минут' :
                return 60;
            case 'ч' :
                return 3600;
            case 'чс' :
                return 3600;
            case 'час' :
                return 3600;
            case 'часов' :
                return 3600;
            case 'ст' :
                return 86400;
            case 'сут' :
                return 86400;
            case 'суток' :
                return 86400;
            case 'мс' :
                return 2592000;
            case 'мес' :
                return 2592000;
            case 'месяц' :
                return 2592000;
            case 'месяцев' :
                return 2592000;
            default :
                return 1;
        }
    }

    function get_time($item) {
        $min = 0;
        $max = 0;
        if (!empty($this->menu_arr [$item] ['time_min'])) {
            if (preg_match('#([0-9]+)([a-zа-я]+)#uim', $this->menu_arr [$item] ['time_min'], $m)) {
                $min = $m [1] * $this->mn($m [2]);
            }
        }
        if (!empty($this->menu_arr [$item] ['time_max'])) {
            if (preg_match('#([0-9]+)([a-zа-я]+)#uim', $this->menu_arr [$item] ['time_max'], $m)) {
                $max = $m [1] * $this->mn($m [2]);
            }
        }
        return array($min, $max);
    }

}
