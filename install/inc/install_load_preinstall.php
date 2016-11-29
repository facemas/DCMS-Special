<?php

class install_load_preinstall {

    var $is_loaded = false;

    function __construct() {
        $settings = &$_SESSION['settings'];

        if (empty($settings)) {
            if ($settings = ini::read(H . '/sys/preinstall/settings.ini', false)) {
                $this->is_loaded = true;
                $settings['mysql_pass'] = ''; // убираем пароль от базы
            }
        } else {
            $this->is_loaded = true;
        }
    }

    function actions() {
        return $this->is_loaded;
    }

    function form() {
        if ($this->is_loaded)
            echo "<span style='font-weight:bold'>" . __('Предустановки успешно загружены') . "</span>";
        // echo text::toOutput(print_r($_SESSION['settings'],1));
        return $this->is_loaded;
    }

}
