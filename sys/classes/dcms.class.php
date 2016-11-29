<?php

/**
 * Базовый класс системы. Объект хранится в глобальной переменной $dcms
 * @property bool debug Включен режим разработчика
 * @property integer ip_long IP пользователя в после ip2long
 * @property string browser_name Название браузера пользователя
 * @property string salt Соль для хэша пароля пользователя. Генерируется при установке движка.
 * @property \log_of_visits log_of_visits
 * @property \log_of_referers log_of_referers
 * @property string mysql_base Название базы данных
 * @property string mysql_host Сервер базы данных
 * @property string mysql_user Имя пользователя базы
 * @property string mysql_pass Пароль пользователя базы
 * @property string language Системный языковой пакет
 * @property int widget_items_count Кол-во отображаемых пунктов в виджете
 * @property bool new_time_as_date
 * @property string browser_type Тип браузера
 * @property bool donate_message флаг, указывающий на то, что сообщение о пожертвовании отправлено
 * @property bool censure Проверка на мат
 * @property int forum_files_upload_size
 * @property string title Заголовок сайта
 * @property string subdomain_light_enable
 * @property string subdomain_mobile_enable
 * @property string subdomain_full_enable
 * @property string browser_type_auto
 * @property string subdomain_light
 * @property string subdomain_mobile
 * @property string subdomain_full
 * @property mixed subdomain_replace_url
 * @property mixed subdomain_main
 * @property mixed subdomain_theme_redirect
 * @property int browser_id
 * @property int update_auto Параметр, отвечающий за автоматическую установку обновлений (0 - выключено, 1 - только проверка, 2 - автоматическое обновление)
 * @property string version Версия SocCMS
 * @property float forum_rating_coefficient Соотношение рейтинга поста с рейтингом пользователя
 * @property int forum_rating_down_balls Стоимость понижения рейтинга поста в форуме
 * @property bool vk_auth_enable
 * @property bool vk_reg_enable
 * @property int vk_app_id
 * @property string vk_app_secret
 * @property bool vk_auth_email_enable
 * @property int img_max_width максимальная ширина изображений на странице
 * @property int theme_version Версия тем оформления
 * @property string theme_light
 * @property string theme_mobile
 * @property string theme_full
 * @property bool check_domain_work
 * @property bool https_only принудительное использование безопасного подключения
 * @property bool https_hsts использование HSTS
 * @property string update_url
 *
 */
class dcms {

    static protected $_instance = null;
    protected $_data = array();

    protected function __construct() {
        // загрузка настроек
        $this->_load_settings();
    }

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * рассылка системных сообщений
     * @param string $mess
     * @param integer $group_min
     */
    public function distribution($mess, $group_min = 2) {
        $group_min = (int) $group_min;
        $q = db::me()->prepare("SELECT `id` FROM `users` WHERE `group` >= ?");
        $q->execute(Array($group_min));
        $users = array();
        while ($ank_ids = $q->fetch()) {
            $users[] = $ank_ids['id'];
        }
        new user($users); // предзагрузка данных пользователей из базы

        foreach ($users as $id_user) {
            $ank = new user($id_user);
            $ank->mess($mess);
        }
    }

    /**
     * Запись действий администратора или системы
     * @global \user $user
     * @param string $module Название модуля
     * @param string $description Описание действия
     * @param boolean $is_system Если сестемное действие
     * @return resource
     */
    public function log($module, $description, $is_system = false) {
        $id_user = 0;

        if (!$is_system) {
            global $user;
            $id_user = $user->id;
        }

        $res = db::me()->prepare("INSERT INTO `action_list_administrators` (`id_user`, `time`, `module`, `description`) VALUES (?, ?, ?, ?)");
        $res->execute(Array($id_user, TIME, $module, $description));
        return true;
    }

    public function __get($name) {
        switch ($name) {
            case 'salt_user':
                return $this->salt . @$_SERVER['HTTP_USER_AGENT'];
                break;
            case 'ip_long':
                return browser::getIpLong();
                break;
            case 'subdomain_main':
                return $this->_subdomain_main();
                break;
            case 'browser_name':
                return browser::getName();
                break;
            case 'browser_type':
                return $this->_browser_type();
                break;
            case 'browser_type_auto':
                return browser::getType();
                break;
            case 'browser_id':
                return $this->_browser_id();
                break;
            case 'items_per_page':
                return $this->_data['items_per_page_' . $this->browser_type];
                break;
            case 'img_max_width':
                return $this->_data['img_max_width_' . $this->browser_type];
                break;
            case 'widget_items_count':
                return $this->_data['widget_items_count_' . $this->browser_type];
                break;
            case 'theme':
                return $this->_data['theme_' . $this->browser_type];
                break;
            default:
                return empty($this->_data[$name]) ? false : $this->_data[$name];
        }
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'items_per_page':
                $name .= '_' . $this->browser_type;
                break;
            case 'theme':
                $name .= '_' . $this->browser_type;
                break;
            case 'img_max_width':
                $name .= '_' . $this->browser_type;
                break;
            case 'widget_items_count':
                $name .= '_' . $this->browser_type;
                break;
        }
        $this->_data[$name] = $value;
        return true;
    }

    protected function _subdomain_main() {
        $domain = preg_replace('/^(wap|pda|web|www|i|touch|mobile|light)\./ui', '', $_SERVER['HTTP_HOST']);
        return $domain;
    }

    /**
     * Тип браузера
     * @return string
     */
    protected function _browser_type() {
        if ($this->subdomain_light_enable) {
            if (0 === strpos($_SERVER['HTTP_HOST'], $this->subdomain_light . '.')) {
                return 'light';
            }
        }
        if ($this->subdomain_mobile_enable) {
            if (0 === strpos($_SERVER['HTTP_HOST'], $this->subdomain_mobile . '.')) {
                return 'mobile';
            }
        }
        if ($this->subdomain_full_enable) {
            if (0 === strpos($_SERVER['HTTP_HOST'], $this->subdomain_full . '.')) {
                return 'full';
            }
        }
        return $this->browser_type_auto;
    }

    protected function _browser_id() {
        static $browser_id = false;

        if (browser::getName() == __('Нет данных')) {
            $browser_id = 0;
        }

        if ($browser_id === false) {
            $q = db::me()->prepare("SELECT * FROM `browsers` WHERE `name` = ? LIMIT 1");
            $q->execute(Array(browser::getName()));
            if ($row = $q->fetch()) {
                $browser_id = $row['id'];
            } else {
                $q = db::me()->prepare("INSERT INTO `browsers` (`type`, `name`) VALUES (?,?)");
                $q->execute(Array(browser::getType(), browser::getName()));
                $browser_id = db::me()->lastInsertId();
            }
        }
        return $browser_id;
    }

    /**
     * Загрузка настроек
     */
    protected function _load_settings() {
        $settings_default = ini::read(H . '/sys/inc/settings.default.ini', true) OR die('Невозможно загрузить файл настроек по-умолчанию');
        if (!$settings = ini::read(H . '/sys/ini/settings.ini')) {
            // если установки небыли загружены, но при этом есть файл установки, то переадресуем на него
            if (file_exists(H . '/install/index.php')) {
                header("Location: /install/");
                exit;
            } else {
                exit('Файл настроек не может быть загружен');
            }
        }
        $this->_data = array_merge($settings_default['DEFAULT'], $this->_data, $settings, $settings_default['REPLACE']);
    }

    /**
     * Сохранение настроек
     * @param \document|boolean $doc
     * @return boolean
     */
    public function save_settings($doc = false) {
        $result = ini::save(H . '/sys/ini/settings.ini', $this->_data);

        if (is_a($doc, 'document')) {
            if ($result) {
                $doc->msg(__('Настройки успешно сохранены'));
            } else {
                $doc->err(__('Нет прав на запись в файл настроек'));
            }
        }

        return $result;
    }

}
