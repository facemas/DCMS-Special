<?php

/**
 * Пользователь
 * @property int id Уникальный идентификатор пользователя
 * @property int group Идентификатор группы пользователя
 * @property string recovery_password ключ для восстановления пароля
 * @property string password хэш пароля
 * @property string login логин
 * @property boolean vis_friends флаг отображения списка друзей
 * @property string group_name название группы пользователя
 * @property int balls кол-во баллов
 * @property float rating рейтинг
 * @property int reg_date дата регистрация (TIMESTAMP)
 * @property int last_visit последнее посещенеие (TIMESTAMP)
 * @property string language Языковой пакет
 * @property bool online Пользователь в сети
 * @property int sex Пол пользователя (0 - женский, 1 - мужской)
 * @property bool is_ban Флаг, указывающий на то, что пользователь забанен
 * @property bool is_ban_full Пользователь забанен без возможности просматривать сайт
 * @property int conversions Кол-во переходов по сайту
 * @property int count_visit Счетчик посещений сайта (авторизаций по cookie, post)
 * @property int is_writeable Флаг, означающий что пользователю разрешено оставлять сообщения на сайте
 * @property mixed nick ник пользователя
 * @property float donate_rub Сумма пожертвований
 * @property int ank_m_r Месяц рождения
 * @property int ank_d_r День рождения
 * @property mixed reg_mail
 * @property int vk_id
 * @property string vk_first_name
 * @property string vk_last_name
 */
class user {

    protected $_update = array();
    protected $_data = array();
    protected $db;

    /**
     *
     * @param boolean|int|array $id_or_arrayToCache Идентификатор пользователя или массив идентификаторов для запроса из базы и помещения в кэш
     */
    function __construct($id_or_arrayToCache = false) {
        $this->db = DB::me();
        if ($id_or_arrayToCache === false) {
            $this->guest_init();
        } elseif (is_array($id_or_arrayToCache)) {
            $this->_usersFromCache($id_or_arrayToCache);
            $this->guest_init();
        } else {
            $this->_user_init($id_or_arrayToCache);
        }
    }

    /**
     * Получение данных сразу нескольких пользователей и помещение их в кэш
     * @staticvar array $cache Массив с кэшем данных пользователей
     * @param array|int $get_users_by_id Массив идентификаторов пользователей
     * @return array Массив данных запрошенных пользователей
     */
    protected function _usersFromCache($get_users_by_id) {
        static $cache = array(); // кэш пользователей
        $get_users_by_id = array_unique((array) $get_users_by_id);

        $users_from_mysql = array(); // пользователи, которые будут запрашиваться из базы (нет в кэше)
        $users_return = array(); // пользователи, которые будут возвращены

        foreach ($get_users_by_id AS $id_user) {
            if (array_key_exists($id_user, $cache)) {
                $users_return[$id_user] = $cache[$id_user];
            } else {
                $users_from_mysql[] = (int) $id_user;
            }
        }

        if ($users_from_mysql) {
            if (!isset($get_user_res)) {
                $get_user_res = $this->db->prepare("SELECT * FROM `users` WHERE `id` IN (?)");
            }
            $get_user_res->execute(Array(implode(',', $users_from_mysql)));

            while ($user_data = $get_user_res->fetch()) {
                $id_user = $user_data['id'];
                $users_return[$id_user] = $cache[$id_user] = $user_data;
            }
        }

        return $users_return;
    }

    /**
     * Инициализация данных неавторизованного пользователя (гостя).
     * Если был инициализирован пользователь, то произойдет запись данных в базу с очисткой текущего объекта
     */
    public function guest_init() {
        $this->save_data();
        $this->_data = array();
        $this->_data ['id'] = false;
        $this->_data ['sex'] = 1;
        $this->_data ['group'] = 0;
    }

    /**
     * инициализация данных пользователя
     * @global \dcms $dcms
     * @staticvar array $cache
     * @param int $id
     */
    protected function _user_init($id) {
        $this->guest_init();

        if ($id === 0) {
            global $dcms;
            // для системных уведомлений
            $this->_data ['id'] = 0;
            $this->_data ['login'] = '[' . $dcms->system_nick . ']';
            $this->_data ['group'] = 6;
            $this->_data ['description'] = __('Системный бот. Создан для уведомлений.');
            return;
        }

        $users = $this->_usersFromCache($id);
        if (array_key_exists($id, $users)) {
            $this->_data = $users[$id];
        }
    }

    /**
     * проверка бана пользователя
     * @staticvar array $is_ban Массив с кэшем забаненых пользователй
     * @return boolean Забанен ли пользователь
     */
    protected function _is_ban() {
        static $is_ban = array();

        if (!isset($is_ban [$this->_data ['id']])) {
            if (!isset($is_ban_res)) {
                $is_ban_res = $this->db->prepare("SELECT * FROM `ban` WHERE `id_user` = ? AND `time_start` < ? AND (`time_end` IS NULL OR `time_end` > ?)");
            }
            $is_ban_res->execute(Array($this->_data['id'], TIME, TIME));
            $is_ban [$this->_data ['id']] = $is_ban_res->fetch();
        }

        return !empty($is_ban [$this->_data ['id']]);
    }

    /**
     * проверка полного (запрет навигации) бана пользователя
     * @staticvar array $is_ban_full Массив с кэшем забаненых пользователей
     * @return boolean Пользователь забанен с запретом навигации по сайту
     */
    protected function _is_ban_full() {
        static $is_ban_full = array();

        if (!isset($is_ban_full [$this->_data ['id']])) {
            if (!isset($is_ban_res)) {
                $is_ban_res = $this->db->prepare("SELECT * FROM `ban` WHERE `id_user` = ? AND `access_view` = '0' AND `time_start` < ? AND (`time_end` IS NULL OR `time_end` > ?)");
            }
            $is_ban_res->execute(Array($this->_data['id'], TIME, TIME));
            $is_ban_full [$this->_data ['id']] = $is_ban_res->fetch();
        }

        return !empty($is_ban_full [$this->_data ['id']]);
    }

    /**
     * проверяет, находится ли пользователь сейчас в онлайне
     * @staticvar array $online Массив с кэшем пользователей, находящихся в данный момент онлайн
     * @param integer $id_user Идентификатор пользователя
     * @return boolean Пользователь онлайн
     */
    protected function _is_online($id_user) {
        static $online = false;
        if ($online === false) {
            $online = array();
            if (!isset($is_online_res)) {
                $is_online_res = $this->db->query("SELECT `id_user` FROM `users_online`");
            }
            while ($on = $is_online_res->fetch()) {
                $online[$on ['id_user']] = true;
            }
        }
        return isset($online[$id_user]);
    }

    /**
     * Проверка на возможность писать сообщения
     * @global \dcms $dcms
     * @return boolean Пользователь может писать сообщения
     */
    protected function _is_writeable() {
        if ($this->_is_ban())
            return false;

        global $dcms;
        if (!$dcms->user_write_limit_hour) {
            // ограничение не установлено
            return true;
        } elseif ($this->_data['group'] >= 2) {
            // пользователь входит в состав администрации
            return true;
        } elseif ($this->_data['reg_date'] < TIME - $dcms->user_write_limit_hour * 3600) {
            // пользователь преодолел ограничение
            return true;
        } else {
            return false;
        }
    }

    /**
     * Возвращает данные пользователя по указанным полям
     * @param $fields
     * @return array
     */
    function getCustomData($fields = array()) {
        $data = array();
        // ключи, которые будут переданы обязательно
        $default = array('id');
        // ключи, которые будут исключены
        $skip = array('_', 'password', 'a_code', 'recovery_password');
        $options = array_merge($default, $fields);

        foreach ($options as $key) {
            if (in_array($key, $skip)) {
                continue;
            }
            $data[$key] = $this->$key;
        }
        return $data;
    }

    /**
     * @return string Ник пользователя с ссылкой на анкету (HTML)
     */
    function show() {
        if ($this->id !== false) {
            return '<a href="/profile.view.php?id=' . $this->id . '">' . $this->nick() . '</a>';
        } else {
            return '[' . __('Нет данных') . ']';
        }
    }

    /**
     * @param bool $html
     * @return string Ник пользователя в HTML
     */
    function nick($html = true) {
        if ($this->id === false) {
            return '<span style="color: grey;">' . __('удален') . '</span>';
        }

        if ($this->vk_id) {
            $login = $this->vk_first_name . ' ' . $this->vk_last_name;
        } else {
            $login = $this->login;
        }

        if (!$html) {
            return $login;
        }

        $ret = array('<span class="' . ($this->online ? 'nick_on' : 'nick_off') . '">' . $login . '</span>');

        if ($this->ank_m_r && $this->ank_d_r) {
            $today_date = date('m-d', mktime(0, 0, 0, date("m"), date("d"), 0));
            $birthday_date = date('m-d', mktime(0, 0, 0, $this->ank_m_r, $this->ank_d_r, 0));
            if ($today_date == $birthday_date) {
                $ret[] = '<span class="nick_birthday"><i class="fa fa-birthday-cake fa-fw"></i></span>';
            }
        }

        return join('', $ret);
    }

    /**
     * @param $msg
     * @param $id_user
     * @return bool
     */
    function mess($msg, $id_user = 0) {
        if (!$this->id) {
            return false;
        }

        $res = DB::me()->prepare("UPDATE `users` SET `mail_new_count` = `mail_new_count` + '1' WHERE `id` = ? LIMIT 1");
        $res->execute(Array($this->id));
        $res = DB::me()->prepare("INSERT INTO `mail` (`id_user`,`id_sender`,`time`,`mess`) VALUES (?, ?, ?, ?)");
        $res->execute(Array($this->id, $id_user, TIME, $msg));
        return true;
    }

    function not($msg, $id_user = 0) {
        if (!$this->id) {
            return false;
        }

        $res = DB::me()->prepare("UPDATE `users` SET `not_new_count` = `not_new_count` + '1' WHERE `id` = ? LIMIT 1");
        $res->execute(Array($this->id));
        $res = DB::me()->prepare("INSERT INTO `notification` (`id_user`,`id_sender`,`time`,`mess`) VALUES (?, ?, ?, ?)");
        $res->execute(Array($this->id, $id_user, TIME, $msg));
        return true;
    }

    /**
     * @param user $ank
     * @return bool
     */
    function is_friend($ank) {
        if (!($ank instanceof user)) {
            $ank = $this;
        }
        if (!$ank->id) {
            return false;
        }
        if ($this->id && $this->id === $ank->id) {
            return true;
        }
        $res = DB::me()->prepare("SELECT COUNT(*) FROM `friends` WHERE `id_user` = ? AND `id_friend` = ? AND `confirm` = '1' LIMIT 1");
        $res->execute(Array($this->id, $ank->id));
        return !!$res->fetchColumn();
    }

    # Иконка пользователя

    function icon() {
        # система
        if ($this->group === 6 && $this->id === 0) {
            return 'support';
        }
        # забаненый пользователь
        if ($this->is_ban) {
            return 'legal';
        }
        # администратор
        if ($this->group >= 2) {
            return 'user-secret';
        }
        # пользователь
        if ($this->group) {
            if ($this->vk_id && $this->vk_first_name && $this->vk_last_name) {
                return 'vk';
            }
            return 'user';
        }
        # гость
        return 'user-o';
    }

    # Получение относительного пути к изображению аватара или false, если аватар отсутствует

    function getAvatar($max_width = 48) {
        $avatar_file_name = $this->id . '.jpg';
        $avatars_path = FILES . '/.avatars'; // папка с аватарами
        $avatars_dir = new files($avatars_path);

        if ($avatars_dir->is_file($avatar_file_name)) {
            $avatar = new files_file($avatars_path, $avatar_file_name);
            return $avatar->getScreen($max_width, 0);
        } else {
            $avatar = new files_file($avatars_path, 'no_foto.jpg');
            return $avatar->getScreen($max_width, 0);
        }
    }

    /**
     * Удаление текущего пользователя
     */
    function delete() {
        $tables = ini::read(H . '/sys/ini/user.tables.ini', true);
        foreach ($tables AS $v) {
            $res = DB::me()->prepare("DELETE FROM " . DB::me()->quote($v['table']) . " WHERE " . DB::me()->quote($v['row']) . " = ?");
            $res->execute(Array($this->id));
        }
        $res = DB::me()->prepare("DELETE FROM `users` WHERE `id` = ?");
        $res->execute(Array($this->id));
        $this->guest_init();
    }

    /**
     *
     * @global \dcms $dcms
     * @param string $n ключ
     * @return mixed значение
     */
    function __get($n) {
        global $dcms;
        switch ($n) {
            case 'language' :
                return empty($this->_data ['language']) ? $dcms->language : $this->_data ['language'];
            case 'is_writeable' :
                return $this->_is_writeable();
            case 'is_ban' :
                return $this->_is_ban();
            case 'is_ban_full' :
                return $this->_is_ban_full();
            case 'online' :
                return (bool) (@$this->_data ['last_visit'] > TIME - SESSION_LIFE_TIME);
            case 'group_name' :
                return groups::name($this->_data ['group']);
            case 'items_per_page' :
                return !empty($this->_data ['items_per_page_' . $dcms->browser_type]) ? $this->_data ['items_per_page_' . $dcms->browser_type] : $dcms->items_per_page;
            case 'theme' :
                return @$this->_data ['theme_' . $dcms->browser_type];
            case 'nick' :
                return @$this->nick(false);
            default :
                return !isset($this->_data [$n]) ? false : $this->_data [$n];
        }
    }

    /**
     *
     * @global \dcms $dcms
     * @param string $n ключ
     * @param string $v значение
     */
    function __set($n, $v) {
        if (empty($this->_data ['id'])) {
            return;
        }
        global $dcms;
        switch ($n) {
            case 'theme' :
                $n .= '_' . $dcms->browser_type;
                break;
            case 'items_per_page' :
                $n .= '_' . $dcms->browser_type;
                break;
            case 'login':
                if ($this->vk_id && $this->vk_first_name && $this->vk_last_name) {
                    return;
                }
                break;
        }

        if (isset($this->_data [$n])) {
            $this->_data [$n] = $v;
            $this->_update [$n] = $v;
        } else {
            trigger_error(__('Поле "%s" не существует', $n));
        }
    }

    public function save_data() {
        if ($this->_update) {
            $sql = array();
            foreach ($this->_update as $key => $value) {
                $sql[] = "`" . $key . "` = " . DB::me()->quote($value);
            }
            DB::me()->query("UPDATE `users` SET " . implode(', ', $sql) . " WHERE `id` = '" . $this->_data ['id'] . "' LIMIT 1");
            $this->_update = array();
        }
    }

    function __destruct() {
        $this->save_data();
    }

}
