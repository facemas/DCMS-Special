<?php

/**
 * Работа с текстом
 */
abstract class text {

    /**
     * Фильтрация текста
     * @param string $str
     * @param int $type
     * @return string
     */
    static function filter($str, $type = 1) {
        switch ($type) {
            case 1: return self::toValue($str);
                break;
            case 2: return self::toOutput($str);
                break;
            default:return $str;
        }
    }

    /**
     * Фильтрация текста с ограничением длины в зависимости от типа браузера.
     * Обрабатывается BBCODE
     * @global \dcms $dcms
     * @param string $text
     * @return string
     */
    static function for_opis($text) {
        global $dcms;
        $text = self::substr($text, $dcms->browser_type == 'full' ? 100000 : 4096);
        $text = self::toOutput($text);
        return $text;
    }

    /**
     * получение кол-ва символов строки
     * Корректная работа с UTF-8
     * @param string $str
     * @return integer
     */
    static function strlen($str) {
        if (function_exists('mb_substr')) {
            return mb_strlen($str);
        }
        if (function_exists('iconv')) {
            return iconv_strlen($str);
        }
        return strlen($str);
    }

    /**
     * Получение подстроки
     * Корректная работа с UTF-8
     * @param string $text Исходная строка
     * @param integer $len Максимальная длина возвращаемой строки
     * @param integer $start Начало подстроки
     * @param string $mn Текст, подставляемый в конец строки при условии, что возхвращаемая строка меньще исходной
     * @return string
     */
    static function substr($text, $len, $start = 0, $mn = ' (...)') {
        $text = trim($text);
        if (function_exists('mb_substr')) {
            return mb_substr($text, $start, $len) . (mb_strlen($text) > $len - $start ? $mn : null);
        }
        if (function_exists('iconv')) {
            return iconv_substr($text, $start, $len) . (iconv_strlen($text) > $len - $start ? $mn : null);
        }

        return $text;
    }

    /**
     * Фильтрация и обработка текста, поступающего от пользователя
     * !!! не защищает от SQL-Inj или XSS
     * @param string $str
     * @return string
     */
    static function input_text($str) {
        // обработка входящего текста
        $str = (string) $str;
        // обработка ника
        //$str = preg_replace_callback('#@([a-zа-яё][a-zа-яё0-9\-\_\ ]{2,31})([\!\.\,\ \)\(]|$)#uim', array('text', 'nick'), $str);
        $str = preg_replace("#(^( |\r|\n)+)|(( |\r|\n)+$)|([^\pL\r\n\s0-9" . preg_quote(' []|`@\'ʼ"-–—_+=~!#:;$%^&*()?/\\.,<>{}©№«»', '#') . "]+)#ui", '', $str);

        $inputbbcode = new inputbbcode($str);
        $str = $inputbbcode->get_html();

        return $str;
    }

    /**
     * Фильтрация и форматирование текста перед вставкой в HTML
     * Производится обработка BBCODE и вставка смайлов
     * @param string $str
     * @return string
     */
    static function toOutput($str) {

        $client = new Client(new Ruleset());
        $client->imageType = 'svg'; // or png (default)
        $client->imagePathPNG = '//soccms.com/sys/images/assets/png/'; // defaults to jsdelivr's free CDN
        $client->imagePathSVG = '//soccms.com/sys/images/assets/svg/'; // defaults to jsdelivr's free CDN
        // преобразование смайлов в BBcode
        $str = smiles::input($str);

        // обработка старых цитат с числом в теге
        $str = preg_replace('#\[(/?)quote_([0-9]+)(\]|\=)#ui', '[\1quote\3', $str);

        // преобразование ссылки на youtube ролик в BBCode
        $str = preg_replace('#(^|\s|\(|\])((https?://)?www\.youtube\.com/watch\?(.*?&)*v=([^ \r\n\t`\'"<]+))(,|\[|<|\s|$)#iuU', '\1[youtube]\5[/youtube]\6', $str);

        // видео vk.com
        $str = preg_replace('#<iframe src="http://vk\.com/video_ext\.php\?oid=([0-9]+)&id=([0-9]+)&hash=([a-z0-9]+)&hd=([0-9])" width="([0-9]+)" height="([0-9]+)" frameborder="0"></iframe>#iuU', '[vk_video oid=\1 id=\2 hash=\3 hd=\4]', $str);

        // преобразование ссылок в тег URL
        $str = preg_replace('#(^|\s|\(|\])([a-z]+://([^ \r\n\t`\'"<]+))(,|\[|<|\s|$)#iuU', '\1[url="\2"]\2[/url]\4', $str);

        // предварительная обработка BBcode
        $prebbcode = new prebbcode($str);
        $str = $prebbcode->get_html();

        $bbcode = new bbcode($str);

        $bbcode->mnemonics['[info]'] = '<img src="/sys/images/icons/info.png" alt="info" />';
        $bbcode->mnemonics['[add]'] = '<img src="/sys/images/icons/bb.add.png" alt="add" />';
        $bbcode->mnemonics['[del]'] = '<img src="/sys/images/icons/bb.del.png" alt="del" />';
        $bbcode->mnemonics['[fix]'] = '<img src="/sys/images/icons/bb.fix.png" alt="fix" />';
        $bbcode->mnemonics['[change]'] = '<img src="/sys/images/icons/bb.change.png" alt="change" />';
        $bbcode->mnemonics['[secure]'] = '<img src="/sys/images/icons/bb.secure.png" alt="secure" />';
        $bbcode->mnemonics['[notice]'] = '<img src="/sys/images/icons/bb.notice.png" alt="notice" />';

        $str = $bbcode->get_html();

        $str = $client->toImage($str);
        $str = $client->unicodeToImage($str);
        $str = $client->shortnameToImage($str);
        //$str = wordwrap($str, 10, "&#173;");

        return $str;
    }

    /**
     * Поиск ника пользователя в тексте сообщения и замена BBCOD`ом
     * @param string $str Текст сообщения
     * @param boolean $replace
     * @return string[]
     */
    static function nickSearch(&$str, $replace = true) {
        if (!db::isConnected()) {
            return false;
        }
        $pattern = '#@([a-zа-яё][a-zа-яё0-9\-\_\ ]{2,31}|\$vk\.[0-9]+)([\!\.\,\ \)\(]|$)#uim';

        $m = array();
        preg_match_all($pattern, $str, $m, PREG_SET_ORDER);
        if ($replace)
            $str = preg_replace_callback($pattern, array('text', 'nick'), $str);

        $logins = array();
        foreach ($m AS $sl) {
            $logins[] = DB::me()->quote($sl[1]);
        }
        $logins = array_unique($logins);

        $users_id = array();

        if ($logins) {
            $q = DB::me()->query("SELECT `id` FROM `users` WHERE `login` IN (" . implode(',', $logins) . ")");
            while ($ank = $q->fetch()) {
                $users_id[] = $ank['id'];
            }
        }
        return $users_id;
    }

    /**
     * Callback для замены ника пользователя в тексте сообщения
     * @param string $value
     * @return string
     */
    static function nick($value) {
        if (!db::isConnected()) {
            // сделано для избежания проблем при установке, когда подключение к базе еще не выполнено
            return $value[1] . $value[2];
        }
        static $q;
        if (!isset($q)) {
            $q = DB::me()->prepare("SELECT `id` FROM `users` WHERE `login` = ? LIMIT 1");
        }
        $q->execute(Array($value[1]));
        if ($ank = $q->fetch()) {
            return '[user]' . $ank['id'] . '[/user]' . $value[2];
        } else {
            return $value[1] . $value[2];
        }
    }

    /**
     * Обработка текста
     * @param string $str
     * @return string
     */
    static function toValue($str) {

        // обработка старых цитат с числом в теге
        $str = preg_replace('#\[(/?)quote_([0-9]+)(\]|\=)#ui', '[\1quote\3', $str);

        // предварительная обработка BBcode
        $bbcode = new prebbcode($str);
        $str = $bbcode->get_html();

        $str = trim(htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));

        return $str;
    }

    /**
     * Возврат строки, разрешенной для названий
     * @param string $text
     * @return string
     */
    static function for_name($text) {
        return trim(preg_replace('#[^\pL0-9\=\?\!\@\\\%/\#\$^\*\(\)\-_\+ ,\.:;]+#ui', '', $text));
    }

    /**
     * Возврат строки, разрешенной для названий файлов
     * @param string $text
     * @return string
     */
    static function for_filename($text) {
        return trim(preg_replace('#(^\.)|[^a-z0-9_\-\(\)\.]+#ui', '_', self::translit($text)));
    }

    /**
     * Транслитерация русского текста в английский
     * @param string $string
     * @return string
     */
    static function translit($string) {
        $table = array(
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Ґ' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Є' => 'YE',
            'Ё' => 'YO',
            'Ж' => 'ZH',
            'З' => 'Z',
            'И' => 'I',
            'І' => 'I',
            'Ї' => 'YI',
            'Й' => 'J',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ў' => 'U',
            'Ф' => 'F',
            'Х' => 'H',
            'Ц' => 'C',
            'Ч' => 'CH',
            'Ш' => 'SH',
            'Щ' => 'CSH',
            'Ь' => '',
            'Ы' => 'Y',
            'Ъ' => '',
            'Э' => 'E',
            'Ю' => 'YU',
            'Я' => 'YA',
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'ґ' => 'g',
            'д' => 'd',
            'е' => 'e',
            'є' => 'ye',
            'ё' => 'yo',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'і' => 'i',
            'ї' => 'yi',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ў' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'csh',
            'ь' => '',
            'ы' => 'y',
            'ъ' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
        );
        return str_replace(array_keys($table), array_values($table), $string);
    }

}
