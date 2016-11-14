<?php

/**
 * UI. Пост в списке постов
 * @property mixed post
 */
class listing_post extends ui {

    public $id = 0;
    public $url = '';
    public $icon = false;
    public $img = false;
    public $counter = false;
    public $time = '';
    public $image = '';
    public $title = '';
    public $content = '';
    public $bottom = '';
    public $highlight = false;
    public $actions = array();
    protected $_old_props = array(
        'post' => 'content',
        'edit' => 'bottom',
        'new' => 'highlight',
        'hightlight' => 'highlight',
    );

    /**
     *
     * @param string $title заголовок поста
     * @param string $content Содержимое поста
     */
    public function __construct($title = '', $content = '') {
        parent::__construct();
        $this->_tpl_file = 'listing.post.tpl';
        $this->id = $this->_data['id'];

        $this->title = $title;
        $this->content = $content;
    }

    public function __get($name) {
        $name = $this->_replace_old_properties($name);
        return $this->$name;
    }

    public function __set($name, $value) {
        $name = $this->_replace_old_properties($name);
        if (isset($this->$name)) {
            $this->$name = $value;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string|url $url
     */
    public function setUrl($url) {
        $this->url = (string) $url;
    }

    /**
     * иконка действия
     * @param string $icon имя системной иконки
     * @param string|url $url путь
     */
    public function action($icon, $url) {
        $design = new design();
        $this->actions[] = array('icon' => (string) $icon, 'url' => (string) $url);
    }

    /**
     * Установка иконки сообщения
     * @param string $icon имя системной иконки
     */
    public function icon($icon) {
        $this->icon = $icon;
    }

    /**
     * Замена старых названий свойств
     * @param string $name
     * @return string
     */
    protected function _replace_old_properties($name) {
        if (isset($this->_old_props[$name])) {
            $name = $this->_old_props[$name];
        }
        return $name;
    }

    public function fetch() {
        $this->_data['id'] = $this->id;
        $this->_data['url'] = $this->url;
        $this->_data['time'] = $this->time;
        $this->_data['title'] = $this->title;
        if (is_array($this->content)) {
            $this->content = text::toOutput(implode("\n", $this->content));
        }
        $this->_data['content'] = $this->content;
        $this->_data['counter'] = $this->counter;
        $this->_data['image'] = $this->image;
        $this->_data['icon'] = $this->icon;
        $this->_data['img'] = $this->img;
        $this->_data['highlight'] = $this->highlight;
        $this->_data['bottom'] = $this->bottom;
        $this->_data['actions'] = $this->actions;
        return parent::fetch();
    }

}
