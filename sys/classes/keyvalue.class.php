<?php

/**
 * Чтение и сохранение данных в/из двумерных(й) массивов из/в файл(а)
 * Получается что то типа INI формата, только без секций
 */
abstract class keyvalue {

    /**
     * Возвращает ассоциативный массив из файла формата key = "value"
     * @param string $path абсолютный путь к файлу на сервере
     * @return boolean|array
     */
    public static function read($path) {
        $array = array();
        if (!$file = @file_get_contents($path)) {
            return false;
        }
        $m = array();
        preg_match_all('/^ \s* (.+) \s* = \s* "(.+)" \s* $/exm', $file, $m, PREG_SET_ORDER);

        for ($i = 0; $i < count($m); $i++) {
            $array[trim($m[$i][1])] = $m[$i][2];
        }
        return $array;
    }

    /**
     * Сохраниение ассоциативного массива в файл
     * @param string $path путь к файлу для сохранения
     * @param array $array двумерный ассоциативный массив
     * @return boolean
     */
    public static function save($path, $array) {
        $tmp_file = TMP . '/tmp.' . passgen() . '.ini';
        $content = array();
        $content[] = ";Saved by keyvalue.class";
        foreach ($array as $key => $value) {
            $content[] = $key . ' = "' . $value . '"';
        }

        // сохраняем во временный файл
        if (!@file_put_contents($tmp_file, implode("\r\n", $content))) {
            return false;
        }
        @chmod($tmp_file, filesystem::getChmodToWrite());

        if (IS_WINDOWS) {
            // в винде файл перед заменой нужно удалить
            if (@file_exists($path) && !@unlink($path)) {
                return false;
            }
        }
        // переименовываем временный файл в нужный нам
        if (!@rename($tmp_file, $path)) {
            return false;
        }

        return true;
    }

}
