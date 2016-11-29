<?php

/**
 * Работа с файлом загруз-центра
 * @property int id Идентификатор файла в базе
 * @property string name Имя файла на сервере
 * @property string runame Локализованное имя файла
 * @property int group_show индекс группы пользователя, которому разрешено видеть файл
 * @property string path_dir_abs Абсолютный путь к папке
 * @property string path_file_abs Абсолютный путь к файлу
 * @property string path_dir_rel Относительный путь к папке
 * @property string path_file_rel относительный путь к файлу
 * @property float rating рейтинг файла
 * @property int rating_count кол-во голосов
 * @property int properties_auto_comlete флаг: Свойства автоматически получены
 * @property int screens_auto_comlete флаг: Скриншоты автоматически получены
 * @property string description Описание файла
 * @property int size Размер файла в байтах
 * @property int time_add Дата добавления файла (TIMESTAMP)
 * @property int time_create Дата создания файла (TIMESTAMP)
 * @property int comments Кол-во комментариев
 * @property int group_edit Индекс группы пользователя, которой разрешено изменять параметры файла
 * @property int id_user Идентификатор пользователя, добавившего файл
 */
class files_file {

    protected $_data = array(); // информация о файле
    protected $_screens = array(); // скриншоты (имена файлов)
    protected $_need_save = false; // необходимость пересохранения сведений о файле
    var $ratings = array();

    /**
     * files_file::__construct()
     *
     * @param string $path_dir_abs
     * @param string $filename
     */
    function __construct($path_dir_abs, $filename) {
        $this->ratings = array(
            -2 => __('Ужасный файл'),
            -1 => __('Плохой файл'),
            0 => __('Без оценки'),
            1 => __('Хороший файл'),
            2 => __('Отличный файл')
        );

        $this->_data['id'] = 0;
        $this->_data['runame'] = convert::to_utf8($filename); // имя на русском (чтобы небыло пустым)
        $this->_data['id_user'] = 0; // создатель файла
        $this->_data['downloads'] = 0; // кол-во скачиваний файла
        $this->_data['description'] = ''; // описание файла (задается вручную)
        $this->_data['description_small'] = ''; // короткое описание файла (задается вручную)
        $this->_data['rating'] = 0; // рейтинг файла
        $this->_data['rating_count'] = 0; // кол-во проголосовавших
        $this->_data['comments'] = 0; // кол-во комментариев
        $this->_data['meta_description'] = '';
        $this->_data['meta_keywords'] = '';

        if ($cfg_ini = ini::read($path_dir_abs . '/.' . $filename . '.ini', true)) {
            // загружаем конфиг
            $this->_data = array_merge($this->_data, (array) @$cfg_ini['CONFIG']);
            $this->_screens = array_merge($this->_screens, (array) @$cfg_ini['SCREENS']);
        } else {
            $dir = new files($path_dir_abs);
            $this->_data['group_show'] = $dir->group_show; // группа, которой доступен файл
            $this->_data['group_edit'] = max($dir->group_write, 2); // группа, которая имеет право на изменение параметров файла
            // если конфиг не загрузился, то предполагаем что файл грузился не средствами движка,
            // поэтому задаем ему время добавления
            $this->_data['time_add'] = TIME; // дата добавления файла
            $this->_need_save = true; // обязательно сохраняем сведения о файле
        }
        $this->_data['name'] = $filename;

        // расширение файла
        $pinfo = pathinfo(strtolower($filename));
        $this->_data['ext'] = $pinfo['extension'];

        $this->_setPathes($path_dir_abs);
        // получение дополнительных сведений о файле
        $this->_getPropertiesAuto();

        if (!$this->id)
            $this->_baseAdd();
    }

    /**
     * переименование файла
     * @param string $runame
     * @param string|bool $name
     * @return boolean
     */
    public function rename($runame, $name = false) {
        if ($this->name{0} == '.')
            return false;

        if ($name && $name{0} == '.')
            return false;

        if ($name && file_exists($this->path_dir_abs . '/' . $name))
            return false;

        if ($name && @rename($this->path_file_abs, $this->path_dir_abs . '/' . $name)) {
            // переименовываем скрины
            foreach ($this->_screens as $scr_key => $scr_file) {
                if (@rename($this->path_dir_abs . '/' . $scr_file, $this->path_dir_abs . '/.' . $name . '.' . $scr_key . '.jpg'))
                    $this->_screens[$scr_key] = '.' . $name . '.' . $scr_key . '.jpg';
            }
            // переименовываем конфиг
            @rename($this->path_dir_abs . '/.' . $this->name . '.ini', $this->path_dir_abs . '/.' . $name . '.ini');
            $this->name = $name;
        }

        $this->runame = $runame;
        return true;
    }

    /**
     * Перемещение файла
     * @global \user $user
     * @param string $path_dir_abs
     * @return boolean
     */
    public function moveTo($path_dir_abs) {
        global $user;

        $dir = new files($path_dir_abs);

        if ($dir->group_show > $user->group || $dir->group_write > $user->group) {
            // если нет прав на просмотр или запись в папку
            return false;
        }

        if (@rename($this->path_file_abs, $dir->path_abs . '/' . $this->name)) {
            // переименовываем скрины
            foreach ($this->_screens as $scr_key => $scr_file) {
                if (@rename($this->path_dir_abs . '/' . $scr_file, $dir->path_abs . '/.' . $this->name . '.' . $scr_key . '.jpg'))
                    $this->_screens[$scr_key] = '.' . $this->name . '.' . $scr_key . '.jpg';
            }
            // переименовываем конфиг
            @rename($this->path_dir_abs . '/.' . $this->name . '.ini', $dir->path_abs . '/.' . $this->name . '.ini');
            $this->path_dir_abs = $dir->path_abs;
            return true;
        }
        return false;
    }

    /**
     * Список доступных ключей (для сортировки)
     * @return array
     */
    public function getKeys() {
        $keys = array();
        if (!empty($this->_data['time_create']))
            $keys['time_create:desc'] = __('Время создания');
        if (!empty($this->_data['downloads']))
            $keys['downloads:desc'] = __('Кол-во скачиваний');
        if (!empty($this->_data['comments']))
            $keys['comments:desc'] = __('Кол-во комментариев');
        if (!empty($this->_data['id_user']))
            $keys['id_user:desc'] = __('Автор');
        if (!empty($this->_data['rating']))
            $keys['rating:desc'] = __('Рейтинг');
        if (!empty($this->_data['title']))
            $keys['title:asc'] = __('Заголовок');
        if (!empty($this->_data['frames']))
            $keys['frames:desc'] = __('Кол-во кадров');
        if (!empty($this->_data['width']))
            $keys['width:desc'] = __('Разрешение');
        if (!empty($this->_data['video_codec']))
            $keys['video_codec:asc'] = __('Видео кодек');
        if (!empty($this->_data['audio_codec']))
            $keys['audio_codec:asc'] = __('Аудио кодек');
        if (!empty($this->_data['playtime_seconds']))
            $keys['playtime_seconds:desc'] = __('Продолжительность');
        if (!empty($this->_data['artist']))
            $keys['artist:asc'] = __('Исполнители');
        if (!empty($this->_data['band']))
            $keys['band:asc'] = __('Группа');
        if (!empty($this->_data['album']))
            $keys['album:asc'] = __('Альбом');
        if (!empty($this->_data['genre']))
            $keys['genre:asc'] = __('Жанр');
        if (!empty($this->_data['track_number']))
            $keys['track_number:asc'] = __('Номер трека');
        if (!empty($this->_data['vendor']))
            $keys['vendor:asc'] = __('Производитель');

        return $keys;
    }

    /**
     * Удаление данного файла и всей дополнительной информации к нему.
     * @return boolean
     */
    public function delete() {
        if (!file_exists($this->path_file_abs) || @unlink($this->path_file_abs)) {
            // удаляем скрины
            if ($this->_screens) {
                foreach ($this->_screens as $num => $scr_file) {
                    $this->screenDelete($num);
                }
            }

            // удаляем конфиг
            @unlink($this->path_dir_abs . '/.' . $this->name . '.ini');

            $this->_baseDelete();
            $dir = new files($this->path_dir_abs);
            $dir->cacheClear();
            $this->_need_save = false;
            $this->__destruct();
            return true;
        }
        return false;
    }

    /**
     * проверяем, можно ли голосовать
     * @global \user $user
     * @param bool|int $set
     * @return int
     */
    public function rating_my($set = false) {
        global $user;
        $q = db::me()->prepare("SELECT `rating` FROM `files_rating` WHERE `id_file` = ? AND `id_user` = ?");
        $q->execute(Array($this->id, $user->id));
        if (!$my_rating = $q->fetch()) {
            $my_rating = 0;
        }

        if ($set !== false && isset($this->ratings[$set])) {
            if ($set && $my_rating) {
                // Изменяем запись
                $my_rating = (int) $set;
                $res = db::me()->prepare("UPDATE `files_rating` SET `rating` = ?, `time` = ? WHERE `id_file` = ? AND `id_user` = ? LIMIT 1");
                $res->execute(Array($my_rating, TIME, $this->id, $user->id));
            } elseif ($set) {
                // Вносим запись
                $my_rating = (int) $set;
                $res = db::me()->prepare("INSERT INTO `files_rating` (`id_file`, `id_user`, `time`, `rating`) VALUES (?, ?, ?, ?)");
                $res->execute(Array($this->id, $user->id, TIME, $my_rating));
            } elseif ($my_rating) {
                // Удаляем запись
                $my_rating = 0;
                $res = db::me()->prepare("DELETE FROM `files_rating` WHERE `id_file` = ? AND `id_user` = ?");
                $res->execute(Array($this->id, $user->id));
            }

            $this->rating_update();
        }

        return $my_rating;
    }

    /**
     * Обновление рейтинга
     */
    public function rating_update() {
        $q = db::me()->prepare("SELECT AVG(`rating`) AS `rating`, COUNT(`id_user`) AS `rating_count` FROM `files_rating` WHERE `id_file` = ?");
        $q->execute(Array($this->_data['id']));
        $data = $q->fetch();
        $this->rating = $data['rating'];
        $this->rating_count = $data['rating_count'];
    }

    /**
     * Извлечение дополнительных сведений о файле
     * @return boolean
     */
    protected function _getPropertiesAuto() {
        if ($this->properties_auto_comlete)
            return;
        if ($desc = files_types::getPropertiesType($this->path_file_abs)) {
            if (@function_exists('set_time_limit')) {
                @set_time_limit(30);
            }
            $propert = "files_properties_$desc";
            $prop_obj = new $propert($this->path_file_abs);
            if ($prop = $prop_obj->getProperties()) {
                $this->_data = array_merge((array) $prop, $this->_data);
            }
        }
        $this->properties_auto_comlete = 1;
    }

    /**
     * Кол-во скриншотов
     * @return int
     */
    public function getScreensCount() {
        $this->_createScreensAuto();
        return count($this->_screens);
    }

    /**
     * Получение скриншота определенного размера (путь в браузере)
     * @param int $img_max_width
     * @param int $num
     * @return string|boolean
     */
    public function getScreen($img_max_width, $num = 0) {
        $this->_createScreensAuto();
        if (!empty($this->_screens[$num])) {
            $screen_path_rel = '/sys/tmp/public.' . md5($this->path_file_rel) . '.time_add' . $this->time_add . '.num' . $num . '.width' . $img_max_width . '.jpg';

            if (file_exists(H . $screen_path_rel))
                return $screen_path_rel;
            if (!$img = @imagecreatefromjpeg($this->path_dir_abs . '/' . $this->_screens[$num]))
                return false;
            $img_screen = imaging::to_screen($img, $img_max_width);

            if ($img_max_width > 48)
                imaging::add_copyright($img_screen);

            if (imagejpeg($img_screen, H . $screen_path_rel, 85))
                return $screen_path_rel;
        }
        return false;
    }

    /**
     * Добавление скриншота
     * @param resource $img
     * @return boolean
     */
    public function screenAdd($img) {
        sort($this->_screens);
        $key = count($this->_screens);
        $scr = '.' . $this->name . '.' . $key . '.jpg';
        if (!@imagejpeg($img, $this->path_dir_abs . '/' . $scr, 85))
            return false;
        $this->_screens[$key] = $scr;
        $this->_need_save = true;
        return true;
    }

    /**
     * Удаление скриншота
     * @param int $num
     * @return boolean
     */
    public function screenDelete($num) {
        if (empty($this->_screens[$num]))
            return false;

        if (@unlink($this->path_dir_abs . '/' . $this->_screens[$num]) || !file_exists($this->path_dir_abs . '/' . $this->_screens[$num])) {
            // удаление уменьшенных копий скриншотов
            $screen_path_tmp = (array) glob(H . '/sys/tmp/public.' . md5($this->path_file_rel) . '.num' . $num . '.width*.jpg');
            foreach ($screen_path_tmp as $path_to_delete) {
                @unlink($path_to_delete);
            }

            unset($this->_screens[$num]);
            sort($this->_screens);
            $this->_need_save = true;
            return true;
        }
        return false;
    }

    /**
     * Удаление скриншотов и установка отметки, что автоматически скриншоты еще не генерировались
     */
    public function screensReset() {
        $count = $this->getScreensCount();
        for ($i = 0; $i < $count; $i++) {
            $this->screenDelete($i);
        }
        $this->screens_auto_comlete = 0;
    }

    /**
     * Автоматическое создание скриншотов
     * @return boolean
     */
    protected function _createScreensAuto() {
        if ($this->screens_auto_comlete)
            return;
        if ($screen = files_types::getScreenType($this->path_file_abs)) {
            $screener = "files_screen_$screen";
            $scr_obj = new $screener($this->path_file_abs);

            if (@function_exists('set_time_limit')) {
                @set_time_limit(30);
            }
            if ($imgs = $scr_obj->getScreen()) {
                $imgs = (array) $imgs;

                foreach ($imgs as $img) {
                    $this->screenAdd($img);
                }
            }
        }
        $this->screens_auto_comlete = 1;
    }

    /**
     * Название иконки по типу файла
     * @return string
     */
    public function icon() {
        return files_types::getIconType($this->path_file_abs);
    }

    /**
     * Ссылка на уменьшенное изображение
     * @param int $size макс. ширина в пикселях
     * @param int $num
     * @return string
     */
    public function image($size = 48, $num = 0) {
        if ($screen = $this->getScreen($size, $num)) {
            return $screen;
        }
        return false;
    }

    /**
     * Установка путей
     * @param string $path_dir_abs
     */
    protected function _setPathes($path_dir_abs) {
        // полный путь к папке
        $this->path_dir_abs = filesystem::unixpath($path_dir_abs);
        // полный путь к файлу
        $this->path_file_abs = $this->path_dir_abs . '/' . $this->name;
        // относительный путь к папке
        $this->path_dir_rel = str_replace(filesystem::unixpath(FILES), '', $this->path_dir_abs);
        // относительный путь к файлу
        $this->path_file_rel = $this->path_dir_rel . '/' . $this->name;
    }

    /**
     * Заносим сведения о файле в базу
     * @return boolean
     */
    protected function _baseAdd() {
        if ($this->id)
            return false;
        if ($this->name{0} == '.')
            return false;

        $res = db::me()->prepare("INSERT INTO `files_cache` (`path_file_rel`, `time_add`, `group_show`, `runame`)
VALUES (?, ?, ?, ?)");
        $res->execute(Array(convert::to_utf8($this->path_file_rel), intval($this->time_add), intval($this->group_show), $this->runame));
        return (bool) $this->id = db::me()->lastInsertId();
    }

    /**
     * обновляем сведения о файле в базе данных
     */
    protected function _baseUpdate() {
        $res = db::me()->prepare("UPDATE `files_cache`
SET `path_file_rel` = ?,
`time_add` = ?,
`group_show` = ?,
`runame` = ?
WHERE `id` = ? LIMIT 1");
        $res->execute(Array(convert::to_utf8($this->path_file_rel), intval($this->time_add), intval($this->group_show), $this->runame, intval($this->id)));
    }

    /**
     * Удаляем сведения о файле из базы данных
     * @return boolean
     */
    protected function _baseDelete() {
        // удаление файла из кэша базы
        $res = db::me()->prepare("DELETE FROM `files_cache` WHERE `id` = ? LIMIT 1");
        $res->execute(Array(intval($this->id)));
        // удаление комментов к файлу
        $res = db::me()->prepare("DELETE FROM `files_comments` WHERE `id_file` = ?");
        $res->execute(Array(intval($this->id)));
        // удаление рейтингов файла
        $res = db::me()->prepare("DELETE FROM `files_rating` WHERE `id_file` = ?");
        $res->execute(Array(intval($this->id)));
        return true;
    }

    /**
     * Возвращение поти в файлу для ссылки
     * @return string
     */
    public function getPath() {
        $path_rel = preg_split('#/+#', $this->path_dir_rel);
        foreach ($path_rel as $key => $value) {
            $path_rel[$key] = urlencode($value);
        }
        return implode('/', $path_rel) . '/' . urlencode($this->name);
    }

    /**
     * размер файла в байтах
     * @return integer
     */
    protected function _getSize() {
        $size = @filesize($this->path_file_abs);
        return $this->size = $size;
    }

    /**
     * Дата и время создания файла в формате UNIXTIMESTAMP
     * @return integer
     */
    protected function _getTimeCreate() {
        $time = @filemtime($this->path_file_abs);
        return $this->time_create = $time;
    }

    function __get($n) {
        global $dcms;
        switch ($n) {
            case 'rating_name':
                return $this->ratings[(int) round($this->rating)];
            case 'description_small':
                return empty($this->_data[$n]) ? text::substr($this->description, $dcms->browser_type == 'full' ? 512 : 256) : $this->_data[$n];
            case 'time_create':
                return isset($this->_data[$n]) ? $this->_data[$n] : $this->_getTimeCreate();
            case 'size':
                return isset($this->_data[$n]) ? $this->_data[$n] : $this->_getSize();
            default:
                return isset($this->_data[$n]) ? $this->_data[$n] : false;
        }
    }

    function __set($n, $v) {
        if (!is_scalar($n) || !is_scalar($v))
            return;

        if (array_key_exists($n, $this->_data) && $this->_data[$n] == $v)
            return;

        if ($n == 'path_dir_abs') {
            $dir_old = new files($this->path_dir_abs);
            $dir_old->cacheClear();
        }

        $this->_data[$n] = $v;
        $this->_need_save = true;

        if (in_array($n, array('screens_auto_comlete', 'properties_auto_comlete', 'path_dir_abs'))) {
            $dir_new = new files($this->path_dir_abs);
            $dir_new->cacheClear();
        }

        if ($n == 'path_dir_abs') {
            $this->_setPathes($this->path_dir_abs);
        }

        if (in_array($n, array('group_show', 'time_add', 'path_file_rel', 'runame')))
            $this->_baseUpdate();
    }

    /**
     * Сохранение информации о файле
     */
    public function save_data() {
        if ($this->name{0} !== '.') {
            ini::save($this->path_dir_abs . '/.' . $this->name . '.ini', array('CONFIG' => $this->_data, 'SCREENS' => $this->_screens), true);
        }
    }

    function __destruct() {
        if ($this->_need_save) {
            $this->save_data();
        }
    }

}
