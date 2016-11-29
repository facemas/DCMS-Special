<?php

/**
 * Список языковых пакетов
 */
abstract class languages {

    /**
     * Проверяет существование языкового пакета
     * @param string $code языковой пакет
     * @return boolean
     */
    static public function exists($code) {
        // проверка на существование языка
        $list = self::getList();
        return isset($list[$code]);
    }

    /**
     * Возвращает конфиг языкового пакета
     * @param string $code
     * @return boolean|array
     */
    static public function getConfig($code) {
        if (!self::exists($code)) {
            return false;
        }
        $list = self::getList();
        return $list[$code];
    }

    /**
     * Возвращает список доступных языковых пакетов
     * @staticvar type $list
     * @return array
     */
    static public function getList() {
        static $list;

        if (isset($list)) {
            return $list;
        }

        // получение списка языковых пакетов
        if ($list = cache::get('languages')) {
            return $list;
        }

        $list = self::getRealList();
        cache::set('languages', $list, 60);

        return $list;
    }

    /**
     * Возвращает список языковых пакетов без использования кэша
     * @return array
     */
    static public function getRealList() {
        $list = array();

        // получение списка языковых пакетов минуя кэш
        $lpath = H . '/sys/languages';
        $od = opendir(H . '/sys/languages');
        while ($rd = readdir($od)) {

            if ($rd {0} == '.') {
                continue; // все файлы и папки начинающиеся с точки пропускаем
            }
            if (is_dir($lpath . '/' . $rd)) {
                if (!file_exists($lpath . '/' . $rd . '/config.ini')) {
                    // если нет конфига, то языковой пакет тоже пропускаем
                    continue;
                }
                $config = ini::read($lpath . '/' . $rd . '/config.ini');
                $enname = empty($config['enname']) ? $rd : $config['enname']; // название языка на английском
                $name = empty($config['name']) ? $rd : $config['name']; // название языка на местном языке
                $icon = empty($config['icon']) ? $rd : $config['icon']; // иконка страны языка
                $xml_lang = empty($config['xml_lang']) ? $rd : $config['xml_lang'];
                $disable_collect_phrases = !empty($config['disable_collect_phrases']);

                $img = file_exists($lpath . '/' . $rd . '/icon.png') ? '/sys/languages/' . $rd . '/icon.png' : false;

                $list[$rd] = array('enname' => $enname, 'name' => $name, 'icon' => $icon, 'img' => $img, 'xml_lang' => $xml_lang, 'disable_collect_phrases' => $disable_collect_phrases);
            }
        }
        closedir($od);

        ksort($list);
        reset($list);

        return $list;
    }

    /**
     * очистка кэша списка языковых пакетов
     */
    static public function clearCache() {
        cache::set('languages', false);
    }

}
