<?php

// Проверяем версию PHP
version_compare(PHP_VERSION, '5.2', '>=') or die('Требуется PHP >= 5.2');

/**
 * Константы и функции, необходимые для работы движка.
 * Выделены в отдельный файл чтобы избежать дублирования кода в инсталляторе
 */
require_once dirname(__FILE__) . '/initialization.php';

/**
 * во время автоматического обновления не должно быть запросов со стороны пользователя
 */
if (cache_events::get('system.update.work')) {
    exit('Выполняется обновление системы. Пожалуйста, обновите страницу позже.');
}

/**
 * @const USER_AGENT
 */
if (!defined('USER_AGENT'))
    define('USER_AGENT', @$_SERVER['HTTP_USER_AGENT']);

/**
 * загрузка системных параметров
 * @global \dcms $dcms Основной объект системы
 */
$dcms = dcms::getInstance();

/**
 *  проверка доступности поддомена.
 *  используется при включении поддомена для определенного типа браузера
 */
if (isset($_GET['check_domain_work'])) {
    echo $dcms->check_domain_work;
    exit;
}

if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') &&
        (empty($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https') &&
        (empty($_SERVER['HTTP_X_FORWARDED_SSL']) || $_SERVER['HTTP_X_FORWARDED_SSL'] !== 'on')
) {
    if ($dcms->https_only) {
        // принудительная переадресация на https
        header("Location: https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
        exit;
    }
} else if ($dcms->https_hsts) {
    // если пользователь уже зашел по https, то говорим браузеру, чтоб он больше не обращался по http
    header("Strict-Transport-Security: max-age=31536000"); // https://ru.wikipedia.org/wiki/HSTS
}

/**
 * переадресация на поддомен, соответствующий типу браузера
 */
if ($dcms->subdomain_theme_redirect && empty($subdomain_theme_redirect_disable)) {
    if ($_SERVER['HTTP_HOST'] === $dcms->subdomain_main) {
        // проверяем, что мы находимся на главном домене, а не на поддомене
        // свойство, в котором хранится значение поддомена для данного типа браузера
        $subdomain_var = "subdomain_" . $dcms->browser_type_auto;
        // свойство, в котором хранится парметр, отвечающий за работоспособность домена
        $subdomain_enable = "subdomain_" . $dcms->browser_type_auto . "_enable";

        if ($dcms->$subdomain_enable) {
            // проверяем, включен ли поддомен для данного типа браузера
            // переадресовываем на соответствующий поддомен
            header('Location: //' . $dcms->$subdomain_var . '.' . $dcms->subdomain_main . $_SERVER ['REQUEST_URI']);
            exit;
        }
    }
}

if (!empty($_SESSION['language']) && languages::exists($_SESSION['language'])) {
    // языковой пакет из сессии
    $user_language_pack = new language_pack($_SESSION['language']);
} else if ($dcms->language && languages::exists($dcms->language)) {
    // системный языковой пакет
    $user_language_pack = new language_pack($dcms->language);
}

// этот параметр будут влиять на счетчики
if ($dcms->new_time_as_date) {
    // новые файлы, темы и т.д. будут отображаться за текущее число
    define('NEW_TIME', DAY_TIME);
} else {
    // новые файлы, темы и т.д. будут отображаться за последние 24 часа
    define('NEW_TIME', TIME - 86400);
}

try {
    $db = DB::me($dcms->mysql_host, $dcms->mysql_base, $dcms->mysql_user, $dcms->mysql_pass);
} catch (ExceptionPdoNotExists $e) {
    @mysql_connect($dcms->mysql_host, $dcms->mysql_user, $dcms->mysql_pass) or die('Нет соединения с MySQL сервером');
    @mysql_select_db($dcms->mysql_base) or die('Нет доступа к выбранной базе данных');
    mysql_query('SET NAMES "utf8"');

    if (false != ($backups = glob(TMP . '/backup.*.zip'))) {
        if (count($backups) == 1) {
            cache_events::set('system.update.work', true, 600);

            $zip = new PclZip($backups[0]);
            $zip->extract(PCLZIP_OPT_PATH, H . '/');

            $tables_exists = new tables(); // тут должна проинклудиться старая версия файла, не использующая PDO
            $table_files = (array) glob(H . '/sys/preinstall/base.create.*.ini');
            $tables = array();
            foreach ($table_files as $table_file) {
                preg_match('#base.create\.(.+)\.ini#ui', $table_file, $m);
                $tables[] = $m[1];
            }

            foreach ($tables as $table) {
                $tab = new table_structure(H . '/sys/preinstall/base.create.' . $table . '.ini');
                if (in_array($table, $tables_exists->tables)) {
                    $tab_old = new table_structure();
                    $tab_old->loadFromBase($table);
                    $sql = $tab_old->getSQLQueryChange($tab);
                } else {
                    $sql = $tab->getSQLQueryCreate();
                }
                mysql_query($sql);
            }

            if ($dcms->update_auto == 2)
                $dcms->update_auto = 1; // отключаем автоматическое обновление

            $admins = groups::getAdmins();
            /** @var $admin \user */
            foreach ($admins AS $admin) {
                $admin->mess("Так как на сервере отсутствует PDO, система была восстановлена из резервной копии.");
            }

            cache_events::set('system.update.work', false);
            die("Обновите страницу");
        }
    }

    die($e->getMessage());
} catch (Exception $e) {
    die('Ошибка подключения к базе данных:' . $e->getMessage());
}

if ($_SERVER['SCRIPT_NAME'] != '/sys/cron.php') {
    /**
     * Поэтапная отправка писем из очереди
     */
    mail::queue_process();

    /**
     * Запись переходов со сторонних сайтов
     * @global \log_of_referers $log_of_referers
     */
    if ($dcms->log_of_referers) {
        $log_of_referers = new log_of_referers();
    }

    /**
     * Запись посещений
     * @global log_of_visits $log_of_visits
     */
    if ($dcms->log_of_visits) {
        $log_of_visits = new log_of_visits();
    }

    /**
     * авторизация пользователя
     * @global \user $user
     */
    if (!empty($_SESSION [SESSION_ID_USER])) {
        // авторизация по сессии
        $user = current_user::getInstance($_SESSION [SESSION_ID_USER]);
    } elseif (!empty($_COOKIE [COOKIE_ID_USER]) && !empty($_COOKIE [COOKIE_USER_PASSWORD]) && !isset($_GET['login_from_cookie']) && $_SERVER ['SCRIPT_NAME'] !== '/pages/login.php' && $_SERVER ['SCRIPT_NAME'] !== '/pages/captcha.php') {
        // авторизация по COOKIE (получение сессии, по которой пользователь авторизуется)
        header('Location: /login.php?login_from_cookie&return=' . URL);
        exit;
    } else {
        // пользователь будет являться гостем
        $user = current_user::getInstance();
    }

    /**
     * удаляем сессию пользователя, если по ней не удалось авторизоваться
     */
    if ($user->id === false && isset($_SESSION [SESSION_ID_USER])) {
        unset($_SESSION [SESSION_ID_USER]);
    }


    /**
     * обработка данных пользователя
     */
    if ($user->id !== false) {
        $user->last_visit = TIME; // запись последнего посещения
        if (AJAX) {
            // при AJAX запросе только обновляем сведения о времени последнего посещения, чтобы пользователь оставался в онлайне
            $res = $db->prepare("UPDATE `users_online` SET `time_last` = ? WHERE `id_user` = ? LIMIT 1");
            $res->execute(Array(TIME, $user->id));
        } else {

            $user->conversions++; // счетчик переходов

            $q = $db->prepare("SELECT * FROM `users_online` WHERE `id_user` = ? LIMIT 1");
            $q->execute(Array($user->id));
            if ($q->fetch()) {
                $res = $db->prepare("UPDATE `users_online` SET `conversions` = `conversions` + '1' , `time_last` = ?, `id_browser` = ?, `ip_long` = ?, `request` = ? WHERE `id_user` = ? LIMIT 1");
                $res->execute(Array(TIME, $dcms->browser_id, $dcms->ip_long, $_SERVER ['REQUEST_URI'], $user->id));
            } else {
                $res = $db->prepare("INSERT INTO `users_online` (`id_user`, `time_last`, `time_login`, `request`, `id_browser`, `ip_long`) VALUES (?, ?, ?, ?, ?, ?)");
                $res->execute(Array($user->id, TIME, TIME, $_SERVER ['REQUEST_URI'], $dcms->browser_id, $dcms->ip_long));
                $user->count_visit++; // счетчик посещений
            }
        }
    } else {
        // обработка гостя
        // зачистка гостей, вышедших из онлайна
        $time_last = TIME - SESSION_LIFE_TIME;
        $res = $db->prepare("DELETE FROM `guest_online` WHERE `time_last` < ?");
        $res->execute(Array($time_last));

        if (!AJAX) {
            // при ajax запросе данные о переходе засчитывать не будем

            $q = $db->prepare("SELECT * FROM `guest_online` WHERE `ip_long` = ? AND `browser_ua` = ? LIMIT 1");
            $q->execute(Array($dcms->ip_long, (string) USER_AGENT));
            if ($q->fetch()) {
                // повторные переходы гостя
                $res = $db->prepare("UPDATE `guest_online` SET `time_last` = ?, `request` = ?, `is_robot` = ?, `domain` = ?, `conversions` = `conversions` + 1 WHERE  `ip_long` = ? AND `browser_ua` = ? LIMIT 1");
                $res->execute(Array(TIME, $_SERVER ['REQUEST_URI'], browser::getIsRobot() ? '1' : '0', $_SERVER['HTTP_HOST'], $dcms->ip_long, (string) USER_AGENT));
            } else {
                // новый гость
                $res = $db->prepare("INSERT INTO `guest_online` (`ip_long`, `browser`, `browser_ua`, `time_last`, `time_start`, `domain`, `request`, `is_robot` ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $res->execute(Array($dcms->ip_long, $dcms->browser_name, (string) USER_AGENT, TIME, TIME, $dcms->subdomain_main, $_SERVER ['REQUEST_URI'], browser::getIsRobot() ? '1' : '0'));
            }
        }
    }

    $cron_time = cache_events::get('cron');
    if ($cron_time < TIME - 180) {
        misc::log('cron не настроен на сервере. вызываем вручную', 'cron');
        include H . '/sys/cron.php';
    }
    unset($cron_time);

    /**
     * при полном бане никуда кроме страницы бана нельзя
     */
    if ($user->is_ban_full && $_SERVER['SCRIPT_NAME'] != '/pages/ban.php') {
        header('Location: /ban.php?' . SID);
        exit;
    }

    /**
     * включаем полный показ ошибок для создателя, если включено в админке
     */
    if ($dcms->debug && $user->group == groups::max() && @function_exists('ini_set')) {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', true);
    }

    /**
     * пользовательский языковой пакет
     */
    if ($user->group && $user->language != $user_language_pack->code && languages::exists($user->language)) {
        $user_language_pack = new language_pack($user->language);
    }
}