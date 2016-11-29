<?php

/**
 * Singleton. Реализация кэширования в файлах. Работает с отложенным сохранением.
 * Все изменения в файлах кэша записываются не при каждом изменении, а только один раз перед завершением работы скрипта.
 */
class cache_file {

    protected static $_instance;
    protected $_files = array();
    protected $_files_modify = array();

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function get($path) {
        if (array_key_exists($path, $this->_files))
            return $this->_files[$path];
        return $this->_files[$path] = @unserialize(@file_get_contents($path));
    }

    public function set($path, $content) {
        $this->_files[$path] = $content;
        $this->_files_modify[$path] = true;
    }

    function __destruct() {
        foreach ($this->_files_modify AS $path => $write) {
            if (!$write)
                continue;

            $content = $this->_files[$path];
            $tmp_file = TMP . '/cache.' . passgen() . '.ser.tmp';

            // удаленный кэш все равно вернет false, поэтому в целях незахламления папки tmp лучше файл удалить
            if (!$content) {
                @unlink($path);
                continue;
            }

            @file_put_contents($tmp_file, serialize($content)) OR die('Ошибка записи кэша');
            @chmod($tmp_file, filesystem::getChmodToWrite());

            if (IS_WINDOWS) {
                // в винде файл перед заменой нужно удалить
                if (@file_exists($path) && !@unlink($path)) {
                    continue;
                }
            }
            // переименовываем временный файл в нужный нам
            if (!@rename($tmp_file, $path)) {
                @unlink($tmp_file);
                die('Ошибка записи кэша');
            }
        }
    }

}

/**
 * Абстрактный класс для кэширования произвольных данных.
 * Используется отложенное сохранение данных при помощи класса cache_file
 */
abstract class cache {

    /**
     * Получение данных из кэша
     * @param string $key
     * @return boolean
     */
    static public function get($key) {
        $path = self::_path($key);

        // чтение данных из файла
        if (!$data = cache_file::getInstance()->get($path))
            return false;

        // проверка актуальности данных
        if ($data ['a'] < TIME) {
            cache_file::getInstance()->set($path, false);
            return false;
        }

        return $data ['d'];
    }

    /**
     * Запись данных в кэш
     * @param string $key
     * @param mixed $data
     * @param int|bool $ttl
     * @return boolean
     */
    static public function set($key, $data, $ttl = false) {
        cache_file::getInstance()->set(self::_path($key), $data ? array('a' => $ttl + TIME, 'd' => $data) : false);
    }

    /**
     * Получение пути к файлу по ключу
     * @param string $key
     * @return string
     */
    static protected function _path($key) {
        return TEMP . '/cache.' . urlencode($key) . '.ser';
    }

}

/**
 * Абстрактный класс. позволяет объединять ключи данных в один кэшируемый файл.
 * Полезно для множества данных, занимающих очень мало места.
 */
abstract class cacher {

    protected static function _read($cache_name, $no_cache = false) {
        static $cache = array();
        if ($no_cache || !isset($cache[$cache_name])) {
            $cache[$cache_name] = cache::get($cache_name);
        }
        return $cache[$cache_name];
    }

    protected static function _max_ttl($cache) {
        //return 1000;
        $max_ttl = 0;
        foreach ($cache as $data) {
            $max_ttl = max($max_ttl, $data['t'] - TIME);
        }
        return $max_ttl;
    }

    public static function get($cache_name, $name) {

        if (!$cache = self::_read($cache_name)) {
            return false;
        }


        if (!isset($cache[$name])) {
            // нет такой переменной
            return false;
        }

        if ($cache[$name]['t'] < TIME) {
            // время жизни переменной вышло
            return false;
        }

        return $cache[$name]['v'];
    }

    /**
     * Удаление устаревших данных
     * @param array $cache
     */
    protected static function _clear(&$cache) {
        // удаление устаревших данных
        foreach ($cache as $name => $data) {
            if ($data['t'] >= TIME)
                continue;
            unset($cache[$name]);
        }
    }

    public static function set($cache_name, $name, $val, $ttl = 0) {
        $cache = (array) self::_read($cache_name, true);
        self::_clear($cache);
        $cache[$name] = array('t' => $ttl + TIME, 'v' => $val);
        cache::set($cache_name, $cache, self::_max_ttl($cache));
        self::_read($cache_name, true);
        return true;
    }

}

/**
 * кэширование счетчиков
 */
abstract class cache_counters extends cacher {

    const cache_name = 'counters';

    public static function get($name) {
        return parent::get(self::cache_name, $name);
    }

    public static function set($name, $val, $ttl = 0) {
        return parent::set(self::cache_name, $name, $val, $ttl);
    }

}

/**
 * кэширование виджетов
 */
abstract class cache_widgets extends cacher {

    const cache_name = 'widgets_content';

    public static function get($name) {
        return parent::get(self::cache_name, $name);
    }

    public static function set($name, $val, $ttl = 0) {
        return parent::set(self::cache_name, $name, $val, $ttl);
    }

}

/**
 * кэширование посещений
 */
abstract class cache_log_of_visits extends cacher {

    const cache_name = 'log_of_visits';

    public static function get($name) {
        return parent::get(self::cache_name, $name);
    }

    public static function set($name, $val, $ttl = 0) {
        return parent::set(self::cache_name, $name, $val, $ttl);
    }

}

/**
 * кэширование информации о дуступе в панель управления (админку)
 */
abstract class cache_dpanel_access extends cacher {

    const cache_name = 'dpanel';

    public static function get($name) {
        return parent::get(self::cache_name, $name);
    }

    public static function set($name, $val, $ttl = 0) {
        return parent::set(self::cache_name, $name, $val, $ttl);
    }

}

/**
 * кэширование неудачных авторизаций
 */
abstract class cache_aut_failture extends cacher {

    const cache_name = 'aut_failture';

    public static function get($name) {
        return parent::get(self::cache_name, $name);
    }

    public static function set($name, $val, $ttl = 0) {
        return parent::set(self::cache_name, $name, $val, $ttl);
    }

}

/**
 * кэширование событий
 */
abstract class cache_events extends cacher {

    const cache_name = 'events';

    public static function get($name) {
        return parent::get(self::cache_name, $name);
    }

    public static function set($name, $val, $ttl = 0) {
        return parent::set(self::cache_name, $name, $val, $ttl);
    }

}
