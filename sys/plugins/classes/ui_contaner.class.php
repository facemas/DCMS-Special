<?php

class ui_contaner extends ui {

    protected $_ui_list = array(); // список содержищихся элементов

    public function count() {
        return count($this->_ui_list);
    }

    public function add($ui) {
        if (!is_a($ui, 'ui')) {
            return false;
        }
        return $this->_ui_list[] = $ui;
    }

    public function fetch() {

        $this->_data['content'] = '';
        foreach ($this->_ui_list AS $post) {
            if (is_a($post, 'ui')) {
                $this->_data['content'] .= $post->fetch();
            }
        }

        return parent::fetch();
    }

}
