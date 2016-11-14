<?php

/**
 * Велосипеды для работы с изображениями
 */
abstract class imaging {

    /**
     * Создание уменьшенной копии изображения
     * @param resource $img
     * @param int $max_width максимальная ширина изображения
     * @return resource
     */
    public static function to_screen($img, $max_width = 200) {
        $x = imagesx($img);
        $y = imagesy($img);

        if ($x > $max_width) {
            $max_height = intval($max_width / $x * $y);
            $img2 = imagecreatetruecolor($max_width, $max_height);
            $white = imagecolorallocate($img2, 255, 255, 255);
            imagefill($img2, $max_width, $max_height, $white);
            imagecopyresampled($img2, $img, 0, 0, 0, 0, $max_width, $max_height, $x, $y);
            return $img2;
        }

        return $img;
    }

    /**
     * Накладывает копирайт на изображение
     * @param resource $img
     * @return boolean
     */
    public static function add_copyright(&$img) {
        if (!$img2 = @imagecreatefrompng(H . '/sys/images/copyright/to_screen.png'))
            return false;
        $x = imagesx($img);
        $y = imagesy($img);
        $x2 = imagesx($img2);
        $y2 = imagesy($img2);

        if ($x < $x2 * 2 || $y < $y2 * 2)
            return false;

        imagecopy($img, $img2, $x - $x2, $y - $y2, 0, 0, $x2, $y2);
    }

    /**
     * Добавляет иконку типа файла на изображение скриншота
     * @param resource $img
     * @param string $path
     * @return boolean
     */
    public static function add_icon(&$img, $path) {
        $type = files_types::getIconType($path);
        if (!$img2 = @imagecreatefrompng(H . '/sys/images/icons_files/' . $type . '.png'))
            return false;
        $x = imagesx($img);
        $y = imagesy($img);
        $x2 = imagesx($img2);
        $y2 = imagesy($img2);

        if ($x < $x2 || $y < $y2)
            return false;

        imagecopy($img, $img2, $x - $x2, $y - $y2, 0, 0, $x2, $y2);
    }

}