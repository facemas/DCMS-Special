<?php

/**
 * Информация о типах файлов
 */
abstract class files_types {

    /**
     * Получение информации о типах файлов
     * @staticvar array $ini
     * @return array
     */
    protected static function _getIni() {
        static $ini = null;
        if ($ini === null) {
            $ini = ini::read(H . '/sys/ini/files.types.ini', true);
        }
        return $ini;
    }

    /**
     * Обработчик для получения описания файла
     * @param string $path
     * @return string
     */
    public static function getPropertiesType($path) {
        $pinfo = pathinfo(strtolower($path));
        $ini = self::_getIni();
        if (!empty($pinfo['extension']) && !empty($ini[$pinfo['extension']]['prop']))
            return $ini[$pinfo['extension']]['prop'];
    }

    /**
     * Обработчик получения скриншота
     * @param string $path
     * @return string
     */
    public static function getScreenType($path) {
        $pinfo = pathinfo(strtolower($path));
        $ini = self::_getIni();
        if (!empty($pinfo['extension']) && !empty($ini[$pinfo['extension']]['screen']))
            return $ini[$pinfo['extension']]['screen'];
    }

    /**
     * получение иконки
     * @param string $path
     * @return string
     */
    public static function getIconType($path) {
        $pinfo = pathinfo(strtolower($path));
        $ini = self::_getIni();
        if (!empty($pinfo['extension']) && !empty($ini[$pinfo['extension']]['icon'])) {
            return $ini[$pinfo['extension']]['icon'];
        }
        return 'file';
    }

    /**
     * получение mime-типа файла по расширению
     * @param string $filename
     * @return string
     */
    public static function get_mime($filename) {
        switch (pathinfo($filename, PATHINFO_EXTENSION)) {
            case 'php':return 'text/plain';
            case 'txt':return 'text/plain';


            case 'emy':return 'text/x-vmel';
            case 'mel':return 'text/x-vmel';
            case 'jad':return 'text/vnd.sun.j2me.app-descriptor';

            // приложения
            case 'jar':return 'application/java-archive';
            case 'thm':return 'application/vnd.eri.thm';
            case 'mpn':return 'application/vnd.mophun.application';
            case 'mpc':return 'application/vnd.mophun.certificate';
            case 'sis':return 'application/vnd.symbian.install';

            // архивы
            case 'zip':return 'application/x-zip-compressed';
            case 'rar':return 'application/x-rar-compressed';
            case '7z':return 'application/x-7z-compressed';
            case 'gz':return 'application/x-gzip';
            case 'hid':return 'application/x-tar';

            case 'tar':return 'application/x-tar';
            // звуки и музыка
            case 'imy':return 'audio/imelody';
            case 'mmf':return 'application/vnd.smaf';
            case 'amr':return 'audio/amr';
            case 'wav':return 'audio/x-wav';
            case 'mp3':return 'audio/mpeg';
            case 'wav':return 'audio/wav';
            case 'midi':return 'audio/midi';
            case 'mid':return 'audio/midi';
            case 'rmf':return 'audio/rmf';

            // видео
            case 'flv':return 'video/flv';
            case 'mp4':return 'video/mp4';
            case '3gp':return 'video/3gpp';
            case '3gpp':return 'video/3gpp';
            // изображения
            case 'jpg':return 'image/jpeg';
            case 'jpeg':return 'image/jpeg';
            case 'gif':return 'image/gif';
            case 'png':return 'image/png';
            case 'bmp':return 'image/bmp';
            case 'tiff':return 'image/tiff';
            case 'tif':return 'image/tiff';
        }

        return 'application/octet-stream';
    }

}