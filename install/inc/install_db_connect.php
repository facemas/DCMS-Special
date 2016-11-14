<?php
class install_db_connect {
    var $is_connected = false;
    var $err_connect = false;
    var $err_db = false;
    var $settings = array();

    function __construct()
    {
        $this->settings = &$_SESSION['settings'];

        if (isset($_POST['mysql_host']))$this->settings['mysql_host'] = $_POST['mysql_host'];
        if (isset($_POST['mysql_user']))$this->settings['mysql_user'] = $_POST['mysql_user'];
        if (isset($_POST['mysql_pass']))$this->settings['mysql_pass'] = $_POST['mysql_pass'];
        if (isset($_POST['mysql_base']))$this->settings['mysql_base'] = $_POST['mysql_base'];

        if (!@mysql_connect($this->settings['mysql_host'], $this->settings['mysql_user'], $this->settings['mysql_pass']))
            $this->err_connect = true;
        elseif (!@mysql_select_db($this->settings['mysql_base']))
            $this->err_db = true;
        else
            $this->is_connected = true;
    }

    function actions()
    {
        return $this->is_connected;
    }

    function form()
    {
        echo "<div style='background-color:" . ($this->err_connect?'#FFADB0':'#ADFFB0') . "'>";
        echo __('Сервер MySQL').":<br /><input type='text' name='mysql_host' value='" . text::toValue($this->settings['mysql_host']) . "' /><br />";
        echo __('Пользователь').":<br /><input type='text' name='mysql_user' value='" . text::toValue($this->settings['mysql_user']) . "' /><br />";
        echo __('Пароль').":<br /><input type='text' name='mysql_pass' value='" . text::toValue($this->settings['mysql_pass']) . "' />";
        echo "</div>";
        echo "<div style='background-color:" . ($this->err_db?'#FFADB0':'#ADFFB0') . "'>";
        echo __('База данных').":<br /><input type='text' name='mysql_base' value='" . text::toValue($this->settings['mysql_base']) . "' />";
        echo "</div>";
        return $this->is_connected;
    }
}

?>