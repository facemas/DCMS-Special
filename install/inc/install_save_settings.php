<?php

class install_save_settings {

    var $is_writed = false;

    function __construct() {
        $this->settings = &$_SESSION['settings'];
        $this->settings['language'] = $_SESSION['language'];
    }

    function actions() {
        $return = false;
        if ($this->is_writed = ini::save(H . '/sys/ini/settings.ini', $this->settings)) {
            $return = true;

            unset($_SESSION);
            session_destroy();

            foreach ($_COOKIE as $key => $value) {
                setcookie($key);
            }

            header("Location: /?" . passgen() . '&' . SID);
            exit;
        }

        return $return;
    }

    function form() {
        echo __('На данном этапе будут сохранены настройки и Вы сможете приступить к использованию движка');

        return true;
    }

    function __destruct() {
        if (!$this->is_writed)
            @unlink(H . '/sys/ini/settings.ini');
    }

}

?>
