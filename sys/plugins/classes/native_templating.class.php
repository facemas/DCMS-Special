<?php

/**
 * Шаблонизатор с PHP синтаксисом
 */
class native_templating {

    public $cache_template = true; // кэширование шаблона в памяти. Используется eval вместо include
    protected $_dir_templates = ''; // папка с файлами шаблонов
    protected $_assigned = array(); // переменные, которые будут переданы в шаблон

    function __construct() {
        
    }

    /**
     * Установка переменной в шаблон
     * @param string $name Ключ переменной
     * @param mixed $value Значение
     * @param int $filter Тип фильтрации. 0 - без фильтрации, 1 - экранирование HTML, 2 - полноценная обработка BBCODE с фильтрацией
     */
    public function assign($name, $value = null, $filter = 0) {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->assign($key, $value);
            }
            return;
        }
        if (is_scalar($name)) {
            $this->_assigned[$name] = text::filter($value, $filter);
        }
    }

    /**
     * Получение обработанного шаблона
     * @param string $tpl_file Путь к файлу шаблона или его имя, если указан dir_template
     * @return string HTML код после обработки
     */
    public function fetch($tpl_file) {
        if (($tpl_path = $this->_getTemplatePath($tpl_file)) === false) {
            return null;
        }
        extract($this->_assigned);
        ob_start();
        if ($this->cache_template)
            @eval('?>' . $this->_getTemplate($tpl_path));
        else
            @include $tpl_path;

        $content = ob_get_clean();
        return $content;
    }

    /**
     * выводим обработанный шаблон
     * @param string $tpl_file Путь к файлу шаблона или его имя, если указан dir_template
     */
    public function display($tpl_file) {
        echo $this->fetch($tpl_file);
    }

    /**
     * получение пути к файлу шаблона
     * @param string $tpl_name Путь к файлу шаблона или его имя, если указан dir_template
     * @return string Путь к файлу шаблона
     */
    protected function _getTemplatePath($tpl_name) {
        if (strpos($tpl_name, 'file:') === 0) {
            $abs_path = text::substr($tpl_name, 256, 5, '');
            $tpl_path = dirname($abs_path) . '/' . basename($abs_path, '.tpl') . '.tpl.php';
        } elseif ($this->_dir_templates) {
            $tpl_path = $this->_dir_templates . '/' . basename($tpl_name, '.tpl') . '.tpl.php';
        } else {
            $tpl_path = $tpl_name;
        }

        if (!file_exists($tpl_path)) {
            return false;
        }

        return $tpl_path;
    }

    /**
     * Получение содержимого шаблона (по возможности из кэша)
     * @staticvar array $templates
     * @param string $tpl_path Путь к файлу шаблона или его имя, если указан dir_template
     * @return string
     */
    protected function _getTemplate($tpl_path) {
        static $templates = array();
        if (!array_key_exists($tpl_path, $templates)) {
            $templates[$tpl_path] = file_get_contents($tpl_path);
        }
        return $templates[$tpl_path];
    }

    /**
     * Перебирает массив, вставляя значения ключей в шаблон
     * @param array $array входной массив. первый уровень перебирается, ключи второго используются для вставки в шаблон значений
     * @param string $tpl шаблон вида <a href="{url}">{name}</a>
     * @param boolean $reverse
     * @return string
     */
    protected function section($array, $tpl, $reverse = false) {
        $return = '';
        if ($reverse)
            $array = array_reverse($array);
        foreach ($array AS $data) {
            $return .= $this->replace($data, $tpl);
        }
        return $return;
    }

    /**
     * Вставка произвольных данных из массива в шаблон
     * @param array $data ассоциативный массив вида $data = array('url' => 'http://...', 'name' => 'Название ссылки')
     * @param string $tpl шаблон вида <a href="{url}">{name}</a>
     * @return string
     */
    protected function replace($data, $tpl) {
        $data = (array) $data;
        $keys = array();
        $values = array();
        foreach ($data AS $key => $value) {
            $keys[] = '{' . $key . '}';
            $values[] = $value;
        }
        return str_replace($keys, $values, $tpl);
    }

}
