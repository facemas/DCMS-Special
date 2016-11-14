<?php

class install_check_platform {

    function check() {
        $check = new check_sys();

        foreach ($check->oks as $ok) {
            echo "$ok<br />";
        }

        foreach ($check->errors as $err) {
            echo "<span style='font-weight:bold'>" . __('Ошибка') . ": $err</span><br />";
        }

        foreach ($check->notices as $note) {
            echo "<span style='font-weight:bold'>" . __('Примечание') . ":</span> $note<br />";
        }

        return empty($check->errors);
    }

    function actions() {
        return $this->check();
    }

    function form() {
        $ok = $this->check();
        if ($ok)
            echo "<span style='font-weight:bold'>" . __('Все зависимости удовлетворены') . "</span>";
        return $ok;
    }

}

?>
