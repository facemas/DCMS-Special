<?php

/**
 * UI. Генератор списка постов, сообщений, файлов и прочей информации, которую можно отобразить списком.
 */
class listing extends ui_contaner {

    public $sortable = false;

    public function __construct() {
        parent::__construct();
        $this->_tpl_file = 'listing.tpl';
    }

    public function setForm($form) {
        if (!is_a($form, 'form')) {
            return;
        }
        $this->_data['form'] = $form;
    }

    /**
     * Добавление поста
     * @return \listing_post
     */
    public function post() {
        return $this->add(new listing_post());
    }

    /**
     * Добавление чекбокса
     * @return \listing_checkbox
     */
    public function checkbox() {
        return $this->add(new listing_checkbox());
    }

    /**
     * получение контента
     * @param string $text_if_empty Текст, отображаемый при отсутствии пунктов
     * @return string
     */
    public function fetch($text_if_empty = '') {

        $this->_data['sortable'] = $this->sortable;

        if ($text_if_empty && !$this->count()) {
            $post = $this->add(new listing_post($text_if_empty));
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