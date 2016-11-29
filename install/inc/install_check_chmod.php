<?php

class install_check_chmod {

    function check() {
        $return = true;

        $nw = ini::read(H . '/sys/ini/chmod.ini');

        $err = array();
        foreach ($nw as $path) {
            $e = check_sys::getChmodErr($path, true);

            echo $path . ' ' . ($e ? '<span style="font-weight:bold">[' . __('Проблема') . ']</span>' : '[OK]') . '<br />';

            $err = array_merge($err, $e);
        }

        if ($err) {
            echo '<textarea>';
            foreach ($err as $error) {
                echo $error . "\r\n";
            }

            echo '</textarea><br />';
            echo '* ' . __('В зависимости от настроек на хостинге, CHMOD для возможности записи должен быть от 644 до 666');
        }else
            echo '<span style="font-weight:bold">' . __('Необходимые права на запись имеются') . '</span>';

        return empty($err);
    }

    function actions() {
        return $this->check();
    }

    function form() {
        return $this->check();
    }

}

?>
