<?php

/**
 * мега велосипед для работы с позициями элементов в массиве.
 */
abstract class arraypos {

    /**
     * Получение позиции элемента в массиве
     * @param array $array Собственно, массив
     * @param mixed $key ключ элемента массива
     * @return int|boolean позиция элемента или false
     */
    static function getPosition($array, $key) {
        $i = 1;
        foreach ($array as $key2 => $value) {
            if ($key2 == $key) {
                return $i;
            }
            $i++;
        }
        return false;
    }

    /**
     * установка позиции элемента в массиве
     * @param array $array массив
     * @param mixed $key ключ
     * @param int $step_to
     * @return boolean
     */
    static function setPosition(&$array, $key, $step_to = 1) {
        if (!array_key_exists($key, $array)) {
            return false;
        }

        $step_to--;
        $step_of = self::getPosition($array, $key) - 1;
        if (!isset($array[$key])) {
            return false;
        }
        $tmp_array = array();

        foreach ($array as $key2 => $value) {
            $tmp_array[] = array('key' => $key2, 'value' => $value);
        }

        if ($step_to == $step_of) {
            // если позиция соответствует
            return true;
        }

        $move = $tmp_array[$step_of];
        if (isset($tmp_array[$step_to])) {
            // опускаем элементы для освобождения требуемой позиции
            $i = count($tmp_array) - 1;
            for ($i; $i >= $step_to; $i--) {
                $tmp_array[$i + 1] = $tmp_array[$i];
            }

            if ($step_of > $step_to) {
                // если у элемента исходная позиция ниже требуемой, значит он был опущен с остальными элементами для освобождения новой позиции
                $step_of++;
            } else {
                $step_to++;
            }
        }
        unset($tmp_array[$step_of]);
        $tmp_array[$step_to] = $move;


        ksort($tmp_array);
        reset($tmp_array);
        $array = array();
        foreach ($tmp_array as $value) {
            $array[$value['key']] = $value['value'];
        }

        return true;
    }

    /**
     * перемещение ключа $key массива $array на $step шагов
     * @param array $array
     * @param mixed $key
     * @param int $step
     * @return boolean
     */
    static function move(&$array, $key, $step = 1) {
        $pos = self::getPosition($array, $key);
        return self::setPosition($array, $key, $pos + $step);
    }

}
