<?php

/**
 * Работа с виджетами
 * @property mixed name
 */
class widget {

    protected $_isset = false;
    protected $_data = array();

    /**
     * Инициализация виджета
     * @param string $path Путь к папке виджета
     */
    function __construct($path) {
        $this->_data ['path_abs'] = realpath($path);
        $this->_data ['runame'] = $this->_data ['name'] = basename($this->_data ['path_abs']);
        $this->_data ['version'] = '1.0'; // версия
        $this->_data ['autor'] = false; // автор
        $this->_data ['script'] = 'index.php'; // исполняемый скрипт
        $this->_data ['screen'] = false; // скриншот
        $this->_data ['cache_by_timeshift'] = false; // отдельный кэш для каждой временной зоны
        $this->_data ['cache_by_language'] = true; // отдельный кэш для каждого языка
        $this->_data ['cache_by_user'] = false; // отдельный кэш для каждого пользователя
        $this->_data ['cache_by_group'] = false; // отдельный кэш для каждой группы
        $this->_data ['cache_by_browser_type'] = false; // отдельный кэш для каждого типа браузера
        $this->_data ['cache_time'] = rand(10, 30); // время хранения кэша в секундах
        $this->_data ['skin'] = 1; // оболочка виджета (не используется, если есть своя)
        if ($config = ini::read($this->_data ['path_abs'] . '/config.ini')) {
            $this->_isset = true;
            // загружаем конфиг
            $this->_data = array_merge($this->_data, (array) @$config);
        }
    }

    /**
     * @return string
     */
    function fetch() {
        if (!$this->_isset) {
            return '';
        }
        if (($content = $this->getContent()) !== false) {
            if (!$this->_data ['skin']) {
                return $content;
            } else {
                $widget = new design ();
                $widget->assign('content', $content);
                $widget->assign('name', $this->_data ['runame']);
                return $widget->fetch('widget.tpl');
            }
        }
        return '';
    }

    /**
     * Выводим сформированный HTML код виджета в браузер
     */
    function display() {
        echo "<!-- Start Widget " . $this->getName() . " -->";
        echo $this->fetch();
        echo "<!-- End Widget " . $this->getName() . " -->";
    }

    /**
     * Возврат содержимого виджета
     * @global \user $user
     * @global \dcms $dcms
     * @return string
     */
    function getContent() {
        if (!$this->_isset) {
            return '';
        }

        if ($cache_content = cache_widgets::get($this->_getCacheId())) {
            return $cache_content;
        }

        global $user, $dcms; // могут использоваться в виджете
        ob_start();
        include $this->_data ['path_abs'] . '/' . $this->_data ['script'];
        $content = ob_get_contents();
        ob_end_clean();

        $cache_time = mt_rand($this->_data ['cache_time'] - 2, $this->_data ['cache_time'] + 2);

        cache_widgets::set($this->_getCacheId(), $content, $cache_time);
        return $content;
    }

    /**
     * уникальный идентификатор в кэше
     * @global \user $user
     * @global \dcms $dcms
     * @global \language_pack $user_language_pack
     * @return boolean
     */
    protected function _getCacheId() {
        if (!$this->_isset) {
            return false;
        }

        global $user, $dcms, $user_language_pack;
        $cache_id = array();

        $cache_id [] = 'wt-' . $this->_data ['name'];

        $design = new design();
        $cache_id [] = 'tm-' . $design->theme->getName();

        $cache_id [] = 'lp-' . $user_language_pack->code;

        if ($this->_data ['cache_by_browser_type']) {
            $cache_id [] = 'bt-' . $dcms->browser_type;
        }

        if ($this->_data ['cache_by_user']) {
            $cache_id [] = 'ur-' . $user->id;
        }

        if ($this->_data ['cache_by_timeshift']) {
            $cache_id [] = 'ts-' . $user->time_shift;
        }

        if ($this->_data ['cache_by_group']) {
            $cache_id [] = 'gp-' . intval($user->group);
        }

        if (SID) {
            // если браузер не поддерживает cookie, то во все ссылки будет добавляться SID,
            // поэтому кэш делаем для каждой сессии свой
            $cache_id [] = 'sn-' . SID;
        }

        return implode('.', $cache_id);
    }

    function __get($n) {
        if (!$this->_isset) {
            return false;
        }

        return isset($this->_data [$n]) ? $this->_data [$n] : false;
    }

    function __set($n, $v) {
        if (!$this->_isset) {
            return;
        }

        if (!isset($this->_data [$n])) {
            return;
        }

        $this->_data [$n] = $v;
    }

    /**
     * Сохранение конфига виджета
     * @return boolean
     */
    function saveData() {
        if (!$this->_isset) {
            return false;
        }

        return ini::save($this->_data ['path_abs'] . '/config.ini', $this->_data);
    }

    public function getName() {
        return $this->name;
    }

    public function getViewName() {
        return $this->runame;
    }

}
