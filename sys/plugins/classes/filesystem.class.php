<?php

/**
 * костыли для работы с файловой системой
 */
abstract class filesystem {

    /**
     * Возвращает массив строк из текстового файла
     * @param string $path Путь к текстовому файлу
     * @return array
     */
    static function fileToArray($path) {
        $array = array();
        $array2 = array();
        if (file_exists($path)) {
            $array2 = file($path);
        }
        foreach ($array2 as $value) {
            $value = trim($value);
            if (!$value) {
                continue;
            }

            $array[] = $value;
        }
        return $array;
    }

    /**
     * Возвращает путь относительно корневой директории сайта
     * @param string|string[] $path абсолютный путь
     * @return string|string[] относительный путь
     */
    static function getRelPath($path) {
        $is_array = false;
        if (is_array($path)) {
            $is_array = true;
        } else {
            $path = (array) $path;
        }

        $replace = self::unixpath(H . '/');

        foreach ($path as $k => $p) {
            $p = self::unixpath($p);
            $path[$k] = str_replace($replace, '', $p);
        }

        return $is_array ? $path : $path[0];
    }

    /**
     * Возвращает оптимальный CHMOD на запись
     * @param bool $is_dir для папки
     * @return int
     */
    static function getChmodToWrite($is_dir = false) {
        if ($is_dir) {
            return 0755;
        } else {
            return 0644;
        }
    }

    /**
     * Возвращает оптимальный CHMOD на чтение
     * @param bool $is_dir для папки
     * @return int
     */
    static function getChmodToRead($is_dir = false) {
        if ($is_dir) {
            return 0500;
        } else {
            return 0400;
        }
    }

    /**
     * Заменяет разделитель директорий на указанный
     * Удаляет повторные разделители
     * @param string $path путь
     * @param string $sep разделитель
     * @return string 
     */
    public static function setPathSeparator($path, $sep = '/') {
        return preg_replace('/[\\\\\/]+/', $sep, $path);
    }

    // получаем путь в стиле *UNIX
    static function unixpath($path) {
        return str_replace('\\', '/', $path);
    }

    static function systempath($path) {
        return str_replace(array('\\', '/'), IS_WINDOWS ? '\\' : '/', $path);
    }

    /**
     * Создание директории с установкой прав на запись
     * @param string $p путь
     * @return boolean
     */
    static function mkdir($p) {
        $p = self::systempath($p);
        if (@mkdir($p, filesystem::getChmodToWrite(true), true)) {
            @chmod($p, filesystem::getChmodToWrite(true));
            return true;
        }
    }

    /**
     * Рекурсивное удаление директории
     * @param string $dir
     * @param boolean $delete_this_dir
     * @return boolean
     */
    static function rmdir($dir, $delete_this_dir = true) {
        $dir = realpath($dir);

        if (!$dir)
            return false;

        $od = opendir($dir);
        while ($rd = readdir($od)) {
            if ($rd == '.' || $rd == '..')
                continue;
            if (is_dir($dir . '/' . $rd)) {
                self::rmdir($dir . '/' . $rd);
            } else {
                chmod($dir . '/' . $rd, filesystem::getChmodToWrite());
                unlink($dir . '/' . $rd);
            }
        }
        closedir($od);


        if ($delete_this_dir) {
            chmod($dir, filesystem::getChmodToWrite(1));
            if (!@rmdir($dir)) {
                // бывает, что с первого раза папка не удаляется, но мы попробуем еще раз с секундной задержкой
                clearstatcache();
                sleep(1);
                return @rmdir($dir);
            }
            return true;
        } else {
            return true;
        }
    }

    /**
     * Получение всех папкок (рекурсивно)
     * @param string $dir путь к директории
     * @return array
     */
    static function getAllDirs($dir) {
        $list = array();

        $dir = realpath($dir);
        $od = opendir($dir);
        while ($rd = readdir($od)) {
            if ($rd == '.' || $rd == '..') {
                continue;
            }
            if (is_dir($dir . '/' . $rd)) {
                $list[] = self::unixpath($dir . '/' . $rd);
                $list_n = self::getAllDirs($dir . '/' . $rd);
                foreach ($list_n as $path) {
                    $list[] = $path;
                }
            }
        }
        closedir($od);
        return $list;
    }

    /**
     * Получение всех файлов (рекурсивно)
     * @param string $dir путь к директории
     * @return array
     */
    static function getAllFiles($dir) {
        $list = array();
        $list_n = array();
        $dir = realpath($dir);
        $od = opendir($dir);
        while ($rd = readdir($od)) {
            if ($rd == '.' || $rd == '..') {
                continue;
            }
            if (is_dir($dir . '/' . $rd)) {
                $list_n[] = self::getAllFiles($dir . '/' . $rd);
            } else {
                $list[] = self::unixpath($dir . '/' . $rd);
            }
        }
        closedir($od);

        foreach ($list_n as $lists) {
            foreach ($lists as $path) {
                $list[] = $path;
            }
        }


        return $list;
    }

    /**
     * Возвращает общий размер всех устаревших временных файлов
     * @return int
     */
    static function getOldTmpFilesSize() {
        $files = self::getOldTmpFiles();
        $size = 0;
        foreach ($files as $path) {
            $size += @filesize($path);
        }
        return $size;
    }

    /**
     * Удаление устаревших временных файлов
     */
    static function deleteOldTmpFiles() {
        if (@function_exists('set_time_limit')) {
            @set_time_limit(300); // ставим ограничение на 5 минут
        }


        $yesterday = TIME - 86400;

        $od = opendir(H . '/sys/tmp');
        while ($rd = readdir($od)) {
            if ($rd {0} === '.') {
                // файлы, начинающиеся с точки пропускаем
                continue;
            }
            if (filemtime(H . '/sys/tmp/' . $rd) > $yesterday) {
                // файл еще не старый
                continue;
            }
            @unlink(H . '/sys/tmp/' . $rd);
        }
        closedir($od);
    }

    /**
     * Ищет все файлы по указанному пути, соответствующие регулярному выражению
     * @param string $path_abs шаблон имени файла
     * @param string $pattern
     * @param boolean $recursive искать во вложенных папках
     * @return array
     */
    public static function getFilesByPattern($path_abs, $pattern = '/.*/', $recursive = false) {
        $list = array();
        $paths = (array) glob(realpath($path_abs) . '/*');

        foreach ($paths as $path) {
            if (is_file($path) && preg_match($pattern, basename($path)))
                $list[] = self::setPathSeparator($path);
            elseif ($recursive)
                $list = array_merge($list, self::getFilesByPattern($path, $pattern, $recursive));
        }

        return $list;
    }

}