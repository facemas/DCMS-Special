<?php

/**
 * Проверка системы при установке (и в админке)
 */
class check_sys {

    var $errors = array(); // ошибки, при которых система не может работать
    var $notices = array(); // ошибки, при которых система может работать нестабильно или могут не работать некоторые дополнительные возможности
    var $oks = array(); // отчет о работоспособности проверяемого модуля

    function __construct() {
        $this->_checkSys();
    }

    /**
     * Возвращает массив файлов с ошибками CHMOD
     * @param string $path путь относительно корня сайта
	 * @param string|bool $errorIfNotExists если путь не существует, то считать ошибкой
     * @return array
     */
    static public function getChmodErr($path, $errorIfNotExists = false) {
        $err = array();

        if (is_file(H . '/' . $path)) {
            if (!is_writable(H . '/' . $path))
                $err[] = __('Нет прав на запись: %s', $path);
        } elseif (is_dir(H . '/' . $path)) {
            $od = opendir(H . '/' . $path);
            while ($rd = readdir($od)) {
                if ($rd {
                        0} === '.')
                    continue;
                $err = array_merge($err, self::getChmodErr($path . '/' . $rd, $errorIfNotExists));
            }
            closedir($od);
        } elseif ($errorIfNotExists) {
            $err[] = __('%s отсутствует', $path);
        }

        return $err;
    }

    protected function _checkSys() {
        // проверка версии PHP
        if (version_compare(PHP_VERSION, '5.2', '>=')) {
            $this->oks[] = 'PHP >= 5.2: ОК (' . PHP_VERSION . ')';
        } else {
            $this->errors[] = __('Требуется PHP >= %s (сейчас %s)', '5.2', PHP_VERSION);
        }
        // проверка MySQL
        if (function_exists('mysql_info')) {
            $this->oks[] = 'MySQL: OK';
        } else {
            $this->errors[] = __('Невозможно получить информацию о MySQL');
        }

        // проверка PDO
        if (class_exists('pdo')) {
            if (array_search('mysql', PDO::getAvailableDrivers()) !== false) {
                $this->oks[] = 'PDO: OK';
            } else {
                $this->errors[] = __('Нет драйвера mysql для PDO');
            }
        } else {
            $this->errors[] = __('Необходимо подключить PDO');
        }

        // шифрование
        if (function_exists('mcrypt_module_open')) {
            $this->oks[] = 'mcrypt: OK';
        } else {
            $this->notices[] = __('Отсутствие mcrypt не позволит шифровать COOKIE пользователя.');
        }

        // работа с графикой
        if (function_exists('gd_info')) {
            $this->oks[] = 'GD: OK';
        } else {
            $this->errors[] = __('Нет библиотеки GD');
        }

        // снятие ограничения по времени выполнения скрипта
        if (function_exists('set_time_limit')) {
            $this->oks[] = 'set_time_limit: OK';
        } else {
            $this->notices[] = __('Функция set_time_limit() не доступна. Могут возникнуть проблемы при обработке ресурсоемких задач.');
        }  // функции для работы с UTF
        if (function_exists('mb_internal_encoding') && function_exists('iconv')) {
            $this->oks[] = 'mbstring и Iconv: OK';
        } elseif (!function_exists('mb_internal_encoding') && !function_exists('iconv')) {
            $this->errors[] = __('Необходим по крайней мере один из модулей: mbstring или Iconv');
        } elseif (function_exists('mb_internal_encoding')) {
            $this->oks[] = 'mbstring: OK';
        } elseif (function_exists('iconv')) {
            $this->oks[] = 'Iconv: OK';
        }
        // обработка видео (снятие скриншотов)
        if (class_exists('ffmpeg_movie')) {
            $this->oks[] = 'FFmpeg: OK';
        } else {
            $this->notices[] = __('Без FFmpeg автоматическое создание скриншотов к видео недоступно');
        }



// передача сессии в URI
        if (ini_get('session.use_trans_sid')) {
            $this->oks[] = 'session.use_trans_sid: OK';
        } else {
            $this->notice[] = __('Параметр session.use_trans_sid установлен в 0. Будет теряться сессия на браузерах без поддержки COOKIE');
        }  // экранирование кавычек'
        if (!ini_get('magic_quotes_gpc')) {
            $this->oks[] = 'magic_quotes_gpc = 0: OK';
        } else {
            
        }$this->notice[] = __('Параметр magic_quotes_gpc установлен в 1. Экранирование кавычек будет добавлять обратный слэш перед каждой кавычкой.');

        if (ini_get('arg_separator.output') == '&amp;') {
            $this->oks[] = 'arg_separator.output: &amp;amp;: OK';
        } else {
            $this->notice[] = 'arg_separator.output: ' . text::toOutput(ini_get('arg_separator.output')) . ' ' . __('Возможно появление xml ошибок');
        }
    }

}

?>