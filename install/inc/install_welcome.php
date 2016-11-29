<?php
class install_welcome {
    function actions()
    {
        return true;
    }

    function form()
    {
        echo __("Добро пожаловать в мастер установки DCMS Special");
        return true;
    }
}

?>
