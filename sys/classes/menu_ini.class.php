<?php

/**
 * UI. Меню /sys/ini/menu.*.ini
 */
class menu_ini {

    public $icons = true; // отображение иконок меню    
    public $menu_arr = array(); // загруженный INI файл меню в массив
    protected $_listings = array();
    protected $_listing;
    protected $_values = array(); // переменные, доступные в строках меню

    function __construct($menu_name = false) {
        if ($menu_name) {
            $this->menu_load($menu_name);
        }
        $this->_listings[] = $this->_listing = new listing();
    }

    protected function menu_load($menu_name) {
        if (($menu_arr = ini::read(H . '/sys/ini/menu.' . $menu_name . '.ini', true)) !== false) {
            $this->menu_arr = $menu_arr;
        } else {
            $post = $this->_listing->post();
            $post->icon('err');
            $post->title = __('Ошибка загрузки меню');
        }
    }

    protected function processing() {
        global $user;
        foreach ($this->menu_arr as $key => $value) {

            if (!empty($value['razdel']) && $this->_listing->count()) {
                $this->_listings[] = $this->_listing = new listing();
            }

            if ($user->group < @$value['group']) {
                continue;
            }
            if (!empty($value['for_vip']) && !$user->is_vip) {
                continue;
            }
            $post = $this->_listing->post();

            if (empty($value['razdel'])) {
                $post->url = text::toValue(@$this->value($value['url']));
            } else {
                $post->highlight = true;
            }

            $post->title = __($key);

            if ($this->icons && !empty($value['icon'])) {
                $post->icon($value['icon']);
            }

            if (isset($value['counter']) && $value['counter'] != NULL) {
                if (is_numeric($value['counter'])) {
                    $post->counter = $value['counter'];
                } elseif (is_file(H . $value['counter'])) {
                    @include H . $value['counter'];
                }
            }
        }
    }

    protected function value($str) {
        if (!$this->_values) {
            return $str;
        }
        return preg_replace('#\{\$(.+?)\}#e', '$this->_values[\\1]', $str);
    }

    public function display() {
        $this->processing();
        foreach ($this->_listings AS $listing) {
            $listing->display();
        }
    }

    public function value_add($name, $value) {
        $this->_values[$name] = $value;
    }

}
