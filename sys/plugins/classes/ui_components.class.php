<?php

/**
 * UI. Генератор списка постов, сообщений, файлов и прочей информации, которую можно отобразить списком.
 */
class ui_components extends ui_contaner {

    public $sortable = false;
    public $ui_comment = false;
    public $ui_image = false;
    public $ui_header = false;
    public $ui_list = false;
    public $ui_feed = false;
    public $ui_segment = false;
    public $ui_divider = false;
    public $class = false;

    public function __construct() {
        parent::__construct();
        $this->_tpl_file = 'component.tpl';
    }

    public function setForm($form) {
        if (!is_a($form, 'form')) {
            return;
        }
        $this->_data['form'] = $form;
    }

    /**
     * Добавление поста
     * @return \ui_compost
     */
    public function post() {
        return $this->add(new ui_compost());
    }

    /**
     * Добавление чекбокса
     * @return \ui_checkbox
     */
    public function checkbox() {
        return $this->add(new ui_checkbox());
    }

    /**
     * получение контента
     * @param string $text_if_empty Текст, отображаемый при отсутствии пунктов
     * @return string
     */
    public function fetch($text_if_empty = '') {

        $this->_data['sortable'] = $this->sortable;
        $this->_data['class'] = $this->class;
        $this->_data['ui_comment'] = $this->ui_comment;
        $this->_data['ui_image'] = $this->ui_image;
        $this->_data['ui_header'] = $this->ui_header;
        $this->_data['ui_feed'] = $this->ui_feed;
        $this->_data['ui_list'] = $this->ui_list;
        $this->_data['ui_segment'] = $this->ui_segment;
        $this->_data['ui_divider'] = $this->ui_divider;

        if ($text_if_empty && !$this->count()) {
            $post = $this->add(new ui_compost($text_if_empty));
            $post->icon('clone');
        }

        return parent::fetch();
    }

    /**
     * отображение
     * @param string $text_if_empty Текст, отображаемый при отсутствии пунктов
     */
    public function display($text_if_empty = '') {
        echo $this->fetch($text_if_empty);
    }

}
