<?php

class install_license {

    function actions() {
        if (!empty($_POST['license_accept'])) {
            return true;
        }
    }

    function form() {
        global $user_language_pack;
        if (is_file(H . '/sys/docs/license_' . $user_language_pack->code . '.txt'))
        $bb = new bb(H . '/sys/docs/license_' . $user_language_pack->code . '.txt'); else
        $bb = new bb(H . '/sys/docs/license.txt');
        $bb->display();
        echo '<br /><label><input type="checkbox" value="1" name="license_accept" />'.__('Принимаю').'</label>';
        return true;
    }

}

?>