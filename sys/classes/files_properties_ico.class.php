<?php

/**
 * Свойства файла ICO
 */
class files_properties_ico {

    protected $_path_abs;

    function __construct($path_abs) {
        $this->_path_abs = $path_abs;
    }

    /**
     * Получение свойств из файла
     * @return array
     */
    public function getProperties() {
        $properties = array();
        $ico = new ico($this->_path_abs);
        $max_w = 0;
        $max_y = 0;
        $cc = 0; // кол-во цветов
        $icons_count = $ico->TotalIcons();
        for ($i = 0; $i < $icons_count; $i++) {
            $icon = $ico->GetIconInfo($i);
            if ($icon['Width'] >= $max_w && $icon['ColorCount'] >= $cc) {
                $cc = $icon['ColorCount'];
                $max_w = $icon['Width'];
                $max_y = $icon['Height'];
            }
        }
        $properties['frames'] = $icons_count;
        $properties['width'] = $max_w;
        $properties['height'] = $max_y;
        $properties['properties'] = $properties['width'] . 'x' . $properties['height'] . ' / ' . $properties['frames'];

        return $properties;
    }

}
