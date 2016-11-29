<?php

/**
 * Тема оформления
 * Class theme
 */
class theme {

    protected
            $_abs_path,
            $_name,
            $_config,
            $_sections,
            $_widgets,
            $_params_default,
            $_params;

    /**
     * @param string $abs_path путь к папке темы оформления (без слэша в конце)
     * @param null|array $config
     * @throws Exception
     */
    public function __construct($abs_path, $config = null) {
        $this->_abs_path = $abs_path;
        $this->_name = basename($this->_abs_path);
        if (!$config) {
            $config = $this->_readConfig();
        }
        $this->_parseConfig($config);
    }

    protected function _readConfig() {
        if (!is_file($this->_abs_path . '/config.ini')) {
            throw new Exception(__('Конфиг не найден'));
        }
        return ini::read($this->_abs_path . '/config.ini', true);
    }

    protected function _parseConfig($config) {
        $required_keys = array('name', 'browsers', 'version', 'echo_section');

        if (empty($config)) {
            throw new Exception(__('Конфиг отсутствует'));
        }
        if (empty($config['CONFIG']) || !is_array($config['CONFIG'])) {
            throw new Exception(__('Параметр %s отсутствует', 'CONFIG'));
        }
        if (empty($config['SECTIONS']) || !is_array($config['SECTIONS'])) {
            throw new Exception(__('Параметр %s отсутствует', 'SECTIONS'));
        }
        if (!isset($config['WIDGETS']) || !is_array($config['WIDGETS'])) {
            throw new Exception(__('Параметр %s отсутствует', 'WIDGETS'));
        }

        $this->_config = $config['CONFIG'];
        $this->_sections = $config['SECTIONS'];
        $this->_widgets = $config['WIDGETS'];

        foreach ($required_keys AS $key) {
            if (!array_key_exists($key, $this->_config)) {
                throw new Exception(__('Параметр %s отсутствует', $key));
            }
        }

        if (empty($config['PARAMS_DEFAULT'])) {
            $this->_params_default = array();
        } else {
            $this->_params_default = $config['PARAMS_DEFAULT'];
        }

        if (empty($config['PARAMS'])) {
            $this->_params = array();
        } else {
            $this->_params = $config['PARAMS'];
        }
    }

    public function getConfigValue($key, $default = null) {
        if (!array_key_exists($key, $this->_config)) {
            return $default;
        }
        return $this->_config[$key];
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParam($key, $default = null) {
        if (!array_key_exists($key, $this->_params_default)) {
            return $default;
        }
        if (array_key_exists($key, $this->_params)) {
            return $this->_params[$key];
        }
        return $this->_params_default[$key];
    }

    /**
     * Запись параметра. Необходимо, чтобы указанный параметр присутствовал в секции [PARAMS_DEFAULT] конфига
     * @param string $key
     * @param string $value
     * @throws Exception
     */
    public function setParam($key, $value) {
        if (!array_key_exists($key, $this->_params_default)) {
            throw new Exception(__('Параметр %s не существует у этой темы'));
        }
        $this->_params[$key] = $value;
        $this->_saveConfig();
    }

    protected function _saveConfig() {
        ini::save($this->_abs_path . '/config.ini', array(
            'CONFIG' => $this->_config,
            'SECTIONS' => $this->_sections,
            'WIDGETS' => $this->_widgets,
            'PARAMS_DEFAULT' => $this->_params_default,
            'PARAMS' => $this->_params
                ), true);
        themes::clearCache();
    }

    /**
     * Имя папки темы
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * Отображаемое имя
     * @return string
     */
    public function getViewName() {
        return $this->getConfigValue('name');
    }

    /**
     * Версия темы оформления
     * @return string
     */
    public function getVersion() {
        return $this->getConfigValue('version');
    }

    /**
     * Массив поддерживаемых типов браузеров
     * @return array
     */
    public function getBrowsers() {
        return preg_split('/[\|\,\:\^]/', $this->getConfigValue('browsers'));
    }

    /**
     * Максимальная ширина изображений
     * return int
     */
    public function getImgWidthMax() {
        return $this->getConfigValue('img_width_max', dcms::getInstance()->img_max_width);
    }

    public function getSections() {
        return $this->_sections;
    }

    public function getEchoSectionKey() {
        return $this->getConfigValue('echo_section');
    }

    /**
     * @param $section
     * @return array
     */
    public function getWidgets($section) {
        if (!array_key_exists($section, $this->_widgets)) {
            return array();
        }
        return explode(',', $this->_widgets[$section]);
    }

    /**
     * @param $section
     * @param array $widgets
     */
    public function setWidgets($section, $widgets = array()) {
        $this->_widgets[$section] = join(',', $widgets);
        $this->_saveConfig();
    }

    public function addWidget($section_key, $widget_name) {
        $widgets = $this->getWidgets($section_key);
        $widgets[] = $widget_name;
        $this->setWidgets($section_key, $widgets);
    }

    public function removeWidget($section_key, $widget_name) {
        $widgets = $this->getWidgets($section_key);
        if (($key = array_search($widget_name, $widgets)) !== false) {
            unset($widgets[$key]);
            $this->setWidgets($section_key, $widgets);
        }
    }

    /**
     * @param $type
     * @return bool
     */
    public function browserSupport($type) {
        return in_array($type, $this->getBrowsers());
    }

}
