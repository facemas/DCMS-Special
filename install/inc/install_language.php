<?php

class install_language {

    function actions() {
        if (!empty($_POST['language'])) {
            if (!languages::exists($_POST['language'])) {
                return false;
            }
        }
        $_SESSION['language'] = $_POST['language'];
        return true;
    }

    function form() {

        $languages = languages::getList();

        foreach ($languages as $key => $l) {
            $checked = ($key == 'russian' ? " checked='checked'" : '');

            echo "<label>";
            echo "<input type='radio' name='language' value='$key'$checked />";
            echo "<i class='{$l['icon']} flag'></i>";
            echo $l['enname'];
            echo "</label><br />";
        }
        return true;
    }

}

?>
