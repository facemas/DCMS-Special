<?php

/**
 * Языковой пакет
 */
class language_pack {

    protected $_isset = false;
    protected $_loaded = false;
    protected $_data = array();
    protected $_default = array();
    protected $_save_default = false;
    protected $_script = 'system';
    var $code;
    var $name;
    var $enname;
    var $icon;
    var $xml_lang;
    var $disable_collect_phrases;

    function __construct($code = false) {
        static $_for_translate;

        if ($code && languages::exists($code)) {
            $this->_isset = true;
            $this->code = $code;
        } else {
            $this->code = false;
            $this->_isset = false;
        }

        if ($config = languages::getConfig($this->code)) {
            $this->enname = $config['enname']; // название языка на английском
            $this->name = $config['name']; // локальное название языка
            $this->icon = $config['icon']; // путь к иконке   
            $this->xml_lang = $config['xml_lang'];
            $this->disable_collect_phrases = $config['disable_collect_phrases'];
        }
        $this->_script = str_replace(array('/', '\\'), '_', $_SERVER['SCRIPT_NAME']);

        if (!$_for_translate) {
            $this->_default = $_for_translate = keyvalue::read(H . '/sys/languages/for_translate.lng');
        } else {
            $this->_default = $_for_translate;
        }
    }

    /**
     * Загрузка данных языкового пакета (используется кэш)
     * @return array
     */
    protected function _load() {
        if ($data = cache::get('language.' . $this->code)) {
            return $data;
        }

        $data = array();
        $lngs = (array) glob(H . '/sys/languages/' . $this->code . '/*.lng');
        foreach ($lngs as $file) {
            $data[basename($file, '.lng')] = keyvalue::read($file);
        }
        cache::set('language.' . $this->code, $data, 60);
        return $data;
    }

    /**
     * Добавление отстутствующей строки
     * @param type $str
     * @return boolean
     */
    protected function _addString($str) {
        if (!$this->code) {
            return false;
        }
        if ($this->disable_collect_phrases) {
            return false;
        }

        $this->_data[$this->_script][$str] = $str;
        keyvalue::save(H . '/sys/languages/' . $this->code . '/' . $this->_script . '.lng', $this->_data[$this->_script]);
        $this->clearCache();
    }

    public function __get($str) {
        return $this->getString($str);
    }

    /**
     * возврат переведенной строки
     * @param string $str строка в коде
     * @return string переведенная строка
     */
    public function getString($str) {
        if (empty($this->_default[$str])) {
            // пополнение общего списка переводимых фраз
            $this->_default[$str] = $str;
            $this->_save_default = true;
        }

        if (!$this->_isset) {
            // если языковой пакет не загружен, то возвращаем строку в исходном виде
            return $str;
        }


        if (!$this->code) {
            // языковой пакет не выбран
            return $str;
        }

        if (!$this->_loaded) {
            // если языковой пакет еще не загружен, то загружаем
            $this->_loaded = true;
            $this->_data = $this->_load();
        }

        if (isset($this->_data[$this->_script][$str])) {
            // перевод найден в спец. словаре
            return $this->_data[$this->_script][$str];
        }

        if (isset($this->_data['system'][$str])) {
            // перевод найден в системном словаре
            return $this->_data['system'][$str];
        }



        $this->_addString($str);
        return $str;
    }

    /**
     * очистка кэша языкового пакета
     */
    function clearCache() {
        cache::set('language.data.' . $this->code, false);
    }

    function __destruct() {
        if ($this->_save_default) {
            keyvalue::save(H . '/sys/languages/for_translate.lng', $this->_default);
        }
    }

}