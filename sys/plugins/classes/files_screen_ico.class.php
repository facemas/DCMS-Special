<?php

/**
 * Получение скриншотов к файлу ICO
 */
class files_screen_ico {

    protected $_path_abs;

    function __construct($path_abs) {
        $this->_path_abs = $path_abs;
    }

    /**
     * Получение массива скриншотов из ICO
     * @return array
     */
    public function getScreen() {
        $ico = new ico($this->_path_abs);
        $get_icon = 0;
        $max_size = 0;
        $max_color = 0;
        $icons = $ico->TotalIcons();
        for ($i = 0; $i < $icons; $i++) {
            $icon = $ico->GetIconInfo($i);
            if ($icon['Width'] > $max_size && $icon['ColorCount'] >= $max_color) {
                $max_color = $icon['ColorCount'];
                $max_size = $icon['Width'];
                $get_icon = $i;
            }
        }
        $images = array();
        $images[] = $ico->GetIcon($get_icon);
        for ($i = 0; $i < $icons; $i++) {
            if ($get_icon == $i)
                continue;
            $images[] = $ico->GetIcon($i);
        }
        return $images;
    }

}