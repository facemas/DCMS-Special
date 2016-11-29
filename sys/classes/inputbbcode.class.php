<?php

/**
 * Обработка BBCODE во входящих сообщениях перед сохранением.
 * Требуется для работы тега img, который скачивает изображение на сервер перед отображением
 */
class inputbbcode extends bbcode
{

    var $info_about_tags = array(
        'img' => array(
            'handler' => 'img_2bb',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array()
        )
    );

    function __construct($code)
    {
        parent::__construct($code);
    }

    function insert_smiles($text)
    {
        return $text;
    }

    function img_2bb($elem)
    {
        if (empty($elem['val'][0]['str'])) {
            return false;
        }
        if (empty($elem['val'][0]['str'])) {
            return false;
        }

        $url = $elem['val'][0]['str'];

        $http = new http_client($url);
        $filename = $http->getFileName();

        if (!$filename) {
            return false;
        }

        $tmp_file = H . '/sys/tmp/bbcode.' . passgen() . '.tmp';

        if (!$http->save_content($tmp_file, 2048576)) {
            @unlink($tmp_file);
            return false;
        }

        if (!$img = @imagecreatefromstring(@file_get_contents($tmp_file))) {
            @unlink($tmp_file);
            return false;
        }
        @unlink($tmp_file);
        $img = imaging::to_screen($img, 1024);


        $id = passgen();
        @imagejpeg($img, H . '/sys/files/.bbcode/' . $id . '.jpg', 80);

        return '[localimg file="' . $id . '.jpg" origin="' . $url . '"]' . $filename . '[/localimg]';
    }

}