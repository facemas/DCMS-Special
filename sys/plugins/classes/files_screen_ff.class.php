<?php

/**
 * Получение скриншота при помощи php_ffmpeg
 */
class files_screen_ff {

    protected $_path_abs;

    function __construct($path_abs) {
        $this->_path_abs = $path_abs;
    }

    /**
     * Получение массива скриншотов из видео
     * @return boolean|array
     */
    public function getScreen() {
        if (!class_exists('ffmpeg_movie')) {
            return false;
        }
        $media = new ffmpeg_movie($this->_path_abs);
        $k_frame = intval($media->getFrameCount());
        $screens = array();
        $k_kadr = 6; // количество кадров
        for ($i = 0; $i < $k_kadr; $i++) {
            $ff_frame = $media->getFrame(intval($k_frame / ($k_kadr / ($i + 1))));
            if (!$ff_frame)
                continue;
            $gd_image = $ff_frame->toGDImage();
            if (!$gd_image)
                continue;
            $screens[] = $gd_image;
        }

        return $screens;
    }

}
