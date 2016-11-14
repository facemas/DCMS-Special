<?php

/**
 * Базовый класс для интерфейса с использованием шаблонов
 */
class ui {

    protected $_tpl_file;
    protected $_data = array();

    public function __get($name) {
        return array_key_exists($name, $this->_data) ? $this->_data[$name] : false;
    }

    public function __construct() {
        $this->_data['id'] = $this->_getNewId();
    }

    /**
     * Возвращает уникальный идентификатор класса на странице
     * @staticvar array $id
     * @return string
     */
    protected function _getNewId() {
        static $id = array();
        $class = get_class($this);
        return $class . '_' . @ ++$id[$class];
    }

    /**
     * Устанавливает путь к файлу для запроса AJAX`ом
     * @param string $url
     */
    public function setAjaxUrl($url) {
        $this->_data['ajax_url'] = $url;
    }

    /**
     * Возврат HTML кода
     * @return string
     */
    public function fetch() {
        $tpl = new design();
        $tpl->assign($this->_data);
        return $tpl->fetch($this->_tpl_file);
    }

    /**
     * Вывод HTML в браузер
     */
    public function display() {
        echo $this->fetch();
    }

}
