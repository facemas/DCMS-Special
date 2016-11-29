<?php

/**
 * Получение скриншота к графическим файлам
 */
class files_screen_image {

    protected $_path_abs;
    protected $_ext;

    function __construct($path_abs) {
        $this->_path_abs = $path_abs;
        $pinfo = pathinfo($this->_path_abs);
        $this->_ext = strtolower($pinfo['extension']);
    }

    /**
     * Возвращает скриншот в GD
     * @return gd2
     */
    public function getScreen() {
        switch ($this->_ext) {
            case 'jpg':return @imagecreatefromjpeg($this->_path_abs);
            case 'jpeg':return @imagecreatefromjpeg($this->_path_abs);
            case 'gif':return @imagecreatefromgif($this->_path_abs);
            case 'png':return @imagecreatefrompng($this->_path_abs);
            default:return false;
        }
    }

}