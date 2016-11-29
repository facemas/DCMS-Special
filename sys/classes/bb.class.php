<?php

/**
 * Работа с текстовыми файлами с BBCODE внутри.
 * Отдельно обрабатывается тег title
 */
class bb {

    var $title = false;
    var $err = false;
    protected $_content = '';
    protected $_pattern_title = "#\[title\](.*?)\[\/title\]#ui";

    function __construct($path) {

        $path = $this->getPath($path);
        if (!$this->_content = @file_get_contents($path)) {
            $this->err = 'Не удалось загрузить файл';
        }
        $this->_content = text::input_text($this->_content);

        $this->title = $this->_getTitle($this->_content);
    }

    protected function _getTitle(&$content) {
        if (preg_match($this->_pattern_title, $content, $matches)) {
            $content = preg_replace($this->_pattern_title, '', $content);
            return $matches[1];
        }
    }

    /**
     * Возвращает содержимое без заголовка
     * @return string
     */
    public function getText() {
        return $this->_content;
    }

    /**
     * Возвращает содержимое с форматированием без заголовка
     * @return string
     */
    public function fetch() {
        return text::toOutput(trim($this->_content));
    }

    /**
     * Отправляет отформатирование содержимое в браузер
     */
    public function display() {
        echo $this->fetch();
    }

    protected function getPath($path) {
        if (false === strpos($path, '{lang}')) {
            return $path;
        }

        global $user;
        $languages = languages::getList();
        $user_lang = $languages[$user->language];


        $path_user_lang = str_replace('{lang}', $user_lang['xml_lang'], $path);
        if (file_exists($path_user_lang)) {
            return $path_user_lang;
        }
    }

}
