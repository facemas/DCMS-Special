<?php

class user_fon {

    public function __construct($user) {
        $this->id = $user;
    }

    # Получаем скриншот
    # $size - расширение

    public function getScreen($size) {
        $file_name = $this->id . '.jpg';
        $file_path = FILES . '/.fon';
        $file_dir = new files($file_path);
        if ($file_dir->is_file($file_name)) {
            $file = new files_file($file_path, $file_name);
            return $file->getScreen($size = 1200, 0);
        } else {
            $file = new files_file($file_path, 'standart.jpg');
            return $file->getScreen($size = 1200, 0);
        }
    }

    /*
     * Выводим скриншот
     * $web - размер для ПК
     * $wap - размер для телефонов
     */

    public function image($web = 700, $wap = 400) {
        global $dcms;
        $size = $dcms->browser_type == 'web' ? $web : $wap;
        if ($screen = $this->getScreen($size)) {
            return $screen;
        }
        return false;
    }

}
