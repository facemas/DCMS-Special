<?php
class install_settings_old_version {
    var $conf = array();
    var $return = false;
    function __construct()
    {
        $this->settings = &$_SESSION['settings'];

        if ($this->conf = @parse_ini_file(H . '/sys/dat/settings.ini', false)) {
            $this->return = true;
        }
    }

    function actions()
    {
        $return = false;
        if (!empty($this->conf['shif'])) {
            // мега важный параметр для авторизации импортированных пользователй
            $this->settings['salt'] = $this->conf['shif'];
            $return = true;
        }

        if (!empty($this->conf['mcrypt_iv']))
            $this->settings['iv'] = $this->conf['mcrypt_iv'];

        return $return;
    }

    function form()
    {
        if (!$this->return) {
            echo "Файл настроек с предыдущей версии не загружен<br />";
            echo "Проверьте файл sys/dat/settings.ini";
        }else {
            echo "Файл настроек с предыдущей версии успешно загружен";
        }

        return $this->return;
    }
}

?>
