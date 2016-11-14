<?php

/**
 * Информация о браузере
 */
abstract class browser {

    /**
     * Возвращает версию IE или false
     * @return integer|false
     */
    static function getIEver() {
        $info = self::getBrowserInfo();
        return $info['ie'];
    }

    /**
     * Возвращает тип браузера
     * @return string light|mobile|full
     */
    static function getType() {
        $info = self::getBrowserInfo();
        return $info['type'];
    }

    /**
     * Возвращает название браузера и его мажорную версию
     * @return string
     */
    static function getName() {
        $info = self::getBrowserInfo();
        return $info['name'];
    }

    /**
     * Возвращает true, если страницу запросил (поисковый) робот
     * @return string
     */
    static function getIsRobot() {
        $info = self::getBrowserInfo();
        return $info['isRobot'];
    }

    /**
     * Возвращает информацию о IP в формате IP_LONG
     * @staticvar boolean|string $ipLong
     * @return string
     */
    static function getIpLong() {
        static $ipLong = false;
        if ($ipLong === false)
            $ipLong = sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
        return $ipLong;
    }

    /**
     * Возвращает информацию о браузере
     * @staticvar boolean|array $info
     * @return array
     */
    static function getBrowserInfo() {
        static $info = false;
        if ($info === false)
            $info = self::_getBrowserinfo();
        return $info;
    }

    static protected function _getBrowserinfo() {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
        $info = array(
            'name' => __('Нет данных'),
            'type' => 'light',
            'ie' => false,
            'isRobot' => false
        );

        // определение большенства ботов
        if (preg_match('#(bot|crawler|cURL|scaner|spider|validator)#ui', $user_agent)) {
            $info['isRobot'] = true;
        }

        // определение названия браузера
        if (preg_match('#^([a-z0-9\-\_ ]+)/([0-9]+\.[0-9]+)#i', $user_agent, $b)) {
            $info['name'] = $b[1] . (!empty($b[2]) ? ' ' . $b[2] : '');
            $info['type'] = 'light';
        }

        // определяем большенство компьтеров
        if (preg_match('#(BSD|Linux|Mac|NT|X11|WOW64)#ui', $user_agent)) {
            $info['type'] = 'full';
        }

        // IE <= 10
        if (preg_match('#MSIE ([0-9]+)#ui', $user_agent, $bv)) {
            $info['name'] = 'Microsoft Internet Explorer';
            $info['type'] = 'full';
            $info['ie'] = $bv[1];
        }

        // IE 11
        if (preg_match('#rv ([0-9]+)#ui', $user_agent, $bv)) {
            $info['name'] = 'Microsoft Internet Explorer';
            $info['type'] = 'web';
            $info['ie'] = $bv[1];
        }

        if (preg_match('#America Online Browser#i', $user_agent)) {
            $info['name'] = 'AOL Explorer';
            $info['type'] = 'full';
        }

        if (preg_match('#(Avant|Advanced) Browser#i', $user_agent)) {
            $info['name'] = 'Avant Browser';
            $info['type'] = 'full';
        }

        if (preg_match('#Camino/([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            $info['name'] = 'Camino ' . $v[1];
            $info['type'] = 'full';
        }

        if (preg_match('#ELinks#i', $user_agent)) {
            $info['name'] = 'ELinks';
            $info['type'] = 'full';
        }

        if (preg_match('#Epiphany#i', $user_agent)) {
            $info['name'] = 'Epiphany';
            $info['type'] = 'full';
        }

        if (preg_match('#Flock#i', $user_agent)) {
            $info['name'] = 'Flock';
            $info['type'] = 'full';
        }

        if (preg_match('#IceWeasel#i', $user_agent)) {
            $info['name'] = 'GNU IceWeasel';
            $info['type'] = 'full';
        }

        if (preg_match('#IceCat#i', $user_agent)) {
            $info['name'] = 'GNU IceCat';
            $info['type'] = 'full';
        }

        if (preg_match('#Microsoft Pocket Internet Explorer#i', $user_agent)) {
            $info['name'] = 'Internet Explorer Mobile';
            $info['type'] = 'light';
        }

        if (preg_match('#MSPIE#i', $user_agent)) {
            $info['name'] = 'Internet Explorer Mobile';
            $info['type'] = 'light';
        }

        if (preg_match('#Windows.+Smartphone#i', $user_agent)) {
            $info['name'] = 'Internet Explorer Mobile';
            $info['type'] = 'light';
        }

        if (preg_match('#Konqueror#i', $user_agent)) {
            $info['name'] = 'Konqueror';
            $info['type'] = 'full';
        }

        if (preg_match('#Links#i', $user_agent)) {
            $info['name'] = 'Links';
            $info['type'] = 'full';
        }

        if (preg_match('#Lynx#i', $user_agent)) {
            $info['name'] = 'Lynx';
            $info['type'] = 'full';
        }

        if (preg_match('#Minimo#i', $user_agent)) {
            $info['name'] = 'Minimo';
            $info['type'] = 'full';
        }

        if (preg_match('#(Firebird|Phoenix|Firefox)/([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            $info['name'] = 'Mozilla Firefox ' . $v[2];
            $info['type'] = 'full';
        }

        if (preg_match('#NetPositive#i', $user_agent)) {
            $info['name'] = 'NetPositive';
            $info['type'] = 'full';
        }

        if (preg_match('#Opera/([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            $ver = self::_browser_version($user_agent);
            $info['name'] = 'Opera ' . ($ver ? $ver : $v[1]);
            $info['type'] = 'full';
        }

        if (preg_match('#Opera Mini/([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            $info['name'] = 'Opera Mini ' . $v[1];
            $info['type'] = 'light';
        }

        if (preg_match('#Opera Mobi#i', $user_agent)) {
            $ver = self::_browser_version($user_agent);
            if ($tel = self::_phone_model($user_agent))
                $info['name'] = 'Opera Mobile' . ($ver ? ' ' . $ver : '') . ' (' . $tel[0] . ')';
            else
                $info['name'] = 'Opera Mobile' . ($ver ? ' ' . $ver : '');
            $info['type'] = 'mobile';
        }

        if (preg_match('#(SymbOS|Symbian).+Opera ([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            if ($tel = self::_phone_model($user_agent))
                $info['name'] = 'Opera Mobile ' . $v[2] . ' (' . $tel[0] . ')';
            else
                $info['name'] = 'Opera Mobile ' . $v[2] . ' (Symbian)';
            $info['type'] = 'mobile';
        }

        if (preg_match('#Windows CE.+Opera ([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            if ($tel = self::_phone_model($user_agent))
                $info['name'] = 'Opera Mobile ' . $v[1] . ' (' . $tel[0] . ')';
            else
                $info['name'] = 'Opera Mobile ' . $v[1] . ' (Win)';
            $info['type'] = 'mobile';
        }

        if (preg_match('#PlayStation Portable#i', $user_agent)) {
            $info['name'] = 'PlayStation Portable';
            $info['type'] = 'full';
        }

        if (preg_match('#Safari#i', $user_agent)) {
            $ver = self::_browser_version($user_agent);
            $info['name'] = 'Safari' . ($ver ? ' ' . $ver : '');
            $info['type'] = 'full';
        }

        if (preg_match('#SeaMonkey#i', $user_agent)) {
            $info['name'] = 'SeaMonkey';
            $info['type'] = 'full';
        }

        if (preg_match('#Shiira#i', $user_agent)) {
            $info['name'] = 'Shiira';
            $info['type'] = 'full';
        }

        if (preg_match('#w3m#i', $user_agent)) {
            $info['name'] = 'w3m';
            $info['type'] = 'full';
        }

        if (preg_match('#Chrome/([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            $info['name'] = 'Google Chrome ' . $v[1];
            $info['type'] = 'full';
        }

        if (preg_match('#YaBrowser/([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            $info['name'] = 'Yandex Browser ' . $v[1];
            $info['type'] = 'full';
        }

        if (preg_match('#OPR/([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            $info['name'] = 'Opera ' . $v[1];
            $info['type'] = 'full';
        }

        if (preg_match('#SONY/COM#i', $user_agent)) {
            $info['name'] = 'Sony mylo';
            $info['type'] = 'light';
        }

        if (preg_match('#Nitro#i', $user_agent)) {
            $info['name'] = 'Nintendo DS';
            $info['type'] = 'light';
        }

        if (preg_match('#^Openwave#i', $user_agent)) {
            $info['name'] = 'Openwave';
            $info['type'] = 'light';
        }

        if (preg_match('#UCWEB#i', $user_agent)) {
            $info['name'] = 'UCWEB';
            $info['type'] = 'light';
        }

        if (preg_match('#BOLT/([0-9]+\.[0-9]+)#i', $user_agent, $m)) {
            $info['name'] = 'BOLT ' . $m[1];
            $info['type'] = 'light';
        }

        if (preg_match('#WAP#ui', $user_agent)) {
            $info['type'] = 'light';
        }

        if ($tel = self::_phone_model($user_agent)) {
            // определение модели телефона
            $info['name'] = $tel[0];
            $info['type'] = $tel[1];
        }

        if (isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) && preg_match('#Opera Mini/([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            $user_agent_opera = $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'];
            if ($tel = self::_phone_model($user_agent_opera)) {
                $info['name'] = 'Opera Mini ' . $v[1] . ' (' . $tel[0] . ')';
                $info['type'] = 'light';
            }
        }

        if (preg_match('#iPhone#i', $user_agent)) {
            $info['name'] = 'iPhone';
            $info['type'] = preg_match('#Opera Mini#i', $user_agent) ? 'light' : 'mobile';
        }

        if (preg_match('#iPod#i', $user_agent)) {
            $info['name'] = 'iPod';
            $info['type'] = preg_match('#Opera Mini#i', $user_agent) ? 'light' : 'mobile';
        }

        if (preg_match('#iPad#i', $user_agent)) {
            $info['name'] = 'iPad';
            $info['type'] = preg_match('#Opera Mini#i', $user_agent) ? 'light' : 'mobile';
        }
        if (preg_match('#Bada#i', $user_agent)) {
            // $info['name'] = 'Samsung Bada';
            $info['type'] = preg_match('#Opera Mini#i', $user_agent) ? 'light' : 'mobile';
        }

        if (preg_match('#Android#i', $user_agent)) {
            if (preg_match('#Opera Mini#i', $user_agent)) {
                $info['name'] = 'Opera Mini (Android)';
                $info['type'] = 'light';
            } else {
                // $info['name'] = 'Android';
                $info['type'] = 'mobile';
            }
        }

        if (preg_match('#Windows Phone#i', $user_agent)) {

            if (preg_match('#Opera Mini#i', $user_agent)) {
                $info['name'] = 'Opera Mini (Windows Phone)';
                $info['type'] = 'light';
            } else {

                // $info['name'] = 'Windows Phone 7';
                $info['type'] = 'mobile';
            }
        }

        if (preg_match('#Googlebot/([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            $info['name'] = 'Googlebot ' . $v[1];
            $info['type'] = 'full';
            $info['isRobot'] = true;
        }

        if (preg_match('#Googlebot-Image/([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            $info['name'] = 'Googlebot-Image ' . $v[1];
            $info['type'] = 'full';
            $info['isRobot'] = true;
        }

        if (preg_match('#Yandex([a-z]+)/([0-9]+\.[0-9]+)#i', $user_agent, $v)) {
            $info['name'] = 'Yandex' . $v[1] . ' ' . $v[2];
            $info['type'] = 'full';
            $info['isRobot'] = true;
        }

        return $info;
    }

    static protected function _browser_version($user_agent) {
        // определение версии браузера
        if (preg_match('#Version/([0-9]+(\.[0-9]+)?)#i', $user_agent, $v))
            return $v[1];
    }

    static protected function _phone_model($ua) {
        // определение модели устройства
        if (preg_match('#SonyEricsson([0-9a-z]+)#i', $ua, $b)) {
            return array('SonyEricsson ' . $b[1], 'light');
        }

        if (preg_match('#Nokia([0-9a-z]+)#i', $ua, $b)) {
            return array('Nokia ' . $b[1], 'light');
        }

        if (preg_match('#LG-([0-9a-z]+)#i', $ua, $b)) {
            return array('LG ' . $b[1], 'light');
        }

        if (preg_match('#FLY( |\-)([0-9a-z]+)#i', $ua, $b)) {
            return array('FLY ' . $b[2], 'light');
        }

        if (preg_match('#MOT-([0-9a-z]+)#i', $ua, $b)) {
            return array('Motorola ' . $b[1], 'light');
        }

        if (preg_match('#SAMSUNG(-SGH|-GT)?-([0-9a-z]+)#i', $ua, $b)) {
            return array('Samsung ' . $b[2], 'light');
        }

        if (preg_match('#SEC-SGH([0-9a-z]+)#i', $ua, $b)) {
            return array('Samsung ' . $b[1], 'light');
        }

        if (preg_match('#SIE-([0-9a-z]+)#i', $ua, $b)) {
            return array('Siemens ' . $b[1], 'light');
        }

        return false;
    }

}
