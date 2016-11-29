<?php

/**
 * UI. Пост с чекбоксом в списке
 * @property string title
 * @property string time
 * @property string name атрибут name
 * @property string content
 * @property boolean checked
 */
class listing_checkbox extends ui
{

    protected $_data = array();

    public function __construct($name = '', $title = '', $checked = false)
    {
        parent::__construct();
        $this->_tpl_file = 'listing.checkbox.tpl';

        $this->_data['id'] = 0;
        $this->_data['title'] = $title;
        $this->_data['name'] = $name;
        $this->_data['checked'] = $checked;
        $this->_data['time'] = false;
        $this->_data['counter'] = false;
        $this->_data['content'] = '';
        $this->_data['bottom'] = '';
        $this->_data['highlight'] = false;
        $this->_data['actions'] = array();
    }

    public function __get($name)
    {
        $name = $this->_replace_old_properties($name);
        return isset($this->_data[$name]) ? $this->_data[$name] : false;
    }

    public function __set($name, $value)
    {
        $name = $this->_replace_old_properties($name);

        if (isset($this->_data[$name])) {
            $this->_data[$name] = $value;
            return true;
        } else {
            return false;
        }
    }

    public function action($icon, $url)
    {
        $design = new design();
        $this->_data['actions'][] = array('icon' => $design->getIconPath($icon), 'url' => $url);
    }

    public function icon($icon)
    {
        $design = new design();
        $this->icon = $design->getIconPath($icon);
    }

    protected function _replace_old_properties($name)
    {
        static $replace = array(
            'post' => 'content',
            'edit' => 'bottom',
            'new' => 'highlight',
            'hightlight' => 'highlight'
        );

        if (isset($replace[$name])) {
            $name = $replace[$name];
        }
        return $name;
    }

}