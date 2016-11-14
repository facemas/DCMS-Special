<?php

class sprite_icon {

    public $path;
    public $w, $h; // размеры
    public $x, $y; // позиция в спрайте
    protected $_image;

    function __construct($path) {
        $this->path = $path;
        $info = getimagesize($this->path);
        $this->w = $info[0];
        $this->h = $info[1];
    }

    function getImage() {
        if (!$this->_image)
            $this->_image = imagecreatefromstring(file_get_contents($this->path));
        return $this->_image;
    }

}

/**
 * Class sprite
 * @property sprite_icon[] _icons
 */
class sprite {

    protected $_icons = array();
    protected
            $_width = 0, // ширина спрайта
            $_height = 0, // высота спрайта
            $_max_width = 512, // максимальная высота спрайта
            $_top_index = 0, // позиция для вставки следующей иконки
            $_left_index = 0; // позиция для вставки следующей иконки
    public $class_prefix = 'DCMS_';

    function __construct() {
        
    }

    function addImages($array) {
        foreach ($array AS $path)
            $this->addImage($path);
    }

    function addImage($path) {
        try {
            $icon = new sprite_icon($path);
            $this->_icons[] = $icon;
        } catch (Exception $e) {
            return false;
        }


        return true;
    }

    function cmp($i1, $i2) {
        if ($i1->h > $i2->h)
            return 1;
        if ($i1->h < $i2->h)
            return -1;
        return 0;
    }

    function bindIndexes() {
        usort($this->_icons, array($this, 'cmp'));

        $this->_width = 0;
        $this->_left_index = 0;
        $this->_top_index = 0;
        $this->_height = 0;

        for ($i = 0; $i < count($this->_icons); $i++) {
            $icon = $this->_icons[$i];

            if ($this->_left_index + $icon->w > $this->_max_width) {
                $this->_left_index = 0;
                $this->_top_index = $this->_height;
            }

            $icon->x = $this->_left_index;
            $icon->y = $this->_top_index;

            $this->_width = max($this->_width, $this->_left_index + $icon->w);
            $this->_height = max($this->_height, $this->_top_index + $icon->h);

            $this->_left_index += $icon->w;
        }
    }

    function saveSpriteImage($path) {
        $img = imagecreatetruecolor($this->_width, $this->_height);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagecolortransparent($img, $black);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        foreach ($this->_icons AS $icon) {
            imagecopy($img, $icon->getImage(), $icon->x, $icon->y, 0, 0, $icon->w, $icon->h);
        }
        imagepng($img, $path);
    }

    function saveSpriteCss($path, $sprite_src, $class_prefix) {
        $css = '.' . $class_prefix . 'icon{' . "\n";
        $css .= 'display: inline-block;' . "\n";
        $css .= 'overflow: hidden;' . "\n";
        $css .= 'background-image: url(' . $sprite_src . ');' . "\n";
        $css .= '}' . "\n";

        foreach ($this->_icons AS $icon) {
            $css .= '.' . implode('.', self::getClassName($icon->path, $class_prefix)) . "{\n";
            $css .= 'width: ' . $icon->w . 'px;' . "\n";
            $css .= 'height: ' . $icon->h . 'px;' . "\n";
            $css .= 'background-position: -' . $icon->x . 'px -' . $icon->y . 'px;' . "\n";
            $css .= "}\n";
        }
        file_put_contents($path, $css);
    }

    static function getClassName($path, $prefix = '') {
        $name = basename($path, '.png');
        $name = str_replace(array('.', '@'), array('_dot_', '_at_'), $name);
        return array($prefix . 'icon', $name);
    }

}
