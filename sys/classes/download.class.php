<?php

/**
 * Отдача файла скриптом с поддержкой докачки. Используется для работы счетчиков скачиваний.
 */
class download {

    public $path;
    public $name;
    public $mime = 'application/octet-stream';

    /**
     * 
     * @param string $name Название файла для отображения в браузере
     * @param string $path Абсолютный путь к файлу на сервере
     */
    function __construct($name, $path) {
        $this->path = $path;
        $this->name = $name;
    }

    /**
     * Существует ли файл на сервере
     * @return bool
     */
    function exists() {
        return is_file($this->path);
    }

    /**
     * Отправляет запрошенное содержимое в браузер
     * @return int кол-во отправленных байт
     */
    function output() {
        $this->mime = $this->get_mime();
        @ob_end_clean();
        $from = 0;
        $to = $size = filesize($this->path);
        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('#bytes=-([0-9]+)#i', $_SERVER['HTTP_RANGE'], $range)) { // если указан отрезок от конца файла
                $from = $size - $range[1]; // начало файла
                $to = $size; // конец файла
            } elseif (preg_match('#bytes=([0-9]+)-#i', $_SERVER['HTTP_RANGE'], $range)) { // если указана только начальная метка
                $from = $range[1]; // начало
                $to = $size; // конец
            } elseif (preg_match('#bytes=([0-9]+)-([0-9]+)#i', $_SERVER['HTTP_RANGE'], $range)) { // если указан отрезок файла
                $from = $range[1]; // начало
                $to = $range[2]; // конец
            }
            header('HTTP/1.1 206 Partial Content');
            $cr = 'Content-Range: bytes ' . $from . '-' . $to . '/' . $size;
        } else {
            header('HTTP/1.1 200 Ok');
        }
        $etag = md5($this->path);
        $etag = substr($etag, 0, 8) . '-' . substr($etag, 8, 7) . '-' . substr($etag, 15, 8);
        header('ETag: "' . $etag . '"');
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . ($to - $from));
        if (isset($cr)) {
            header($cr);
        }
        header('Connection: close');
        header('Content-Type: ' . $this->mime);
        header('Last-Modified: ' . gmdate('r', filemtime($this->path)));
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($this->path)) . ' GMT');
        header('Expires: ' . gmdate('D, d M Y H:i:s', TIME + 3600) . ' GMT');
        $f = fopen($this->path, 'rb');

        if (!preg_match('#^image/#i', $this->mime)) {
            // если файл не является изображением, то его отображать в браузере не нужно
            header('Content-Disposition: attachment; filename=' . convert::of_utf8(basename($this->name)));
        }


        if (@function_exists('ini_set')) {
            // при использовании встроенного сжатия возникали проблемы со скачиванием файлов
            ini_set('zlib.output_compression', 'Off');
        }

        fseek($f, $from, SEEK_SET);
        $size = $to;
        $downloaded = 0;
        while (!feof($f) and ! connection_status() and ( $downloaded < $size)) {
            $block = min(1024 * 8, $size - $downloaded);
            echo fread($f, $block);
            $downloaded += $block;
            flush();
        }
        fclose($f);
        return $downloaded; // возвращаем кол-во скачаных байт
    }

    /**
     * получение mime-типа файла по расширению
     * @return string
     */
    function get_mime() {
        return files_types::get_mime($this->name);
    }

}
