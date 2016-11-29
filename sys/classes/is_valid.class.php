<?php

/**
 * Проверка валидности различных данных
 */
abstract class is_valid {

    /**
     * Проверка на соответствие WMID
     * @param string $wmid
     * @return boolean
     */
    static function wmid($wmid) {
        if (preg_match("#^[0-9]{12}$#", $wmid))
            return true;
    }

    /**
     * Проверка на соответствие логину skype
     * @param string $skype
     * @return boolean
     */
    static function skype($skype) {
        if (preg_match("#^[a-z][a-z0-9_\-\.]{5,31}$#ui", $skype))
            return true;
    }

   
    static function telnumber($login) {
        if (preg_match('#^\+?([0-9]+)$#', $login, $m)) {
            return '+' . $m[1];
        }
        return $login;
    }

    /**
     * Проверяет на возможность использования строки в качестве ника
     * @param string $nick
     * @return boolean
     */
    static function nick($nick) {
        // проверка на длину логина и возможные символы
        if (!preg_match("#^[a-zа-яё][a-zа-яё0-9\-\_\ ]{2,31}$#ui", $nick)) {
            return false;
        }
        // запрещаем использовать ряд логинов (которые могут ввести в оману новичков)
        if (preg_match("#(bot|DCMS|deleted|moder|system|бот|модер|систем)#ui", $nick)) {
            return false;
        }
        // запрещаем одновременное использование кирилицы и латинского алфавита
        if (preg_match("#[a-z]+#ui", $nick) && preg_match("#[а-яё]+#ui", $nick)) {
            return false;
        }
        // пробелы вначале или конце ника недопустимы
        if (preg_match("#(^\ )|(\ $)#ui", $nick)) {
            return false;
        }
        return true;
    }

    /**
     * Проверка на соответствие email
     * @param string $mail
     * @return boolean
     */
    static function mail($mail) {
        if (preg_match('#^[a-z0-9\-\._]+\@([a-z0-9-_]+\.)+([a-z0-9]{2,4})\.?$#ui', $mail))
            return true;
    }

    /**
     * Проверяет на соответствие паролю
     * @param string $pass
     * @return boolean
     */
    static function password($pass) {
        if (preg_match("#^[a-zа-яё0-9\-\_\ ]{6,32}$#ui", $pass)) {
            return true;
        }
    }

    /**
     * Проверка ника на подозрительность
     * @param string $str
     * @return boolean
     */
    static function suspicion($str) {
        // три и более согласных подряд
        if (preg_match('#[БВГДЖЗКЛМНПРСТФХЦЧШЩBCDFGHJKLMNPQRSTVXZ]{4,}#ui', $str, $m)) {
            return $m[0];
        }

        // повторение одного символа или выражения более 3-х раз подряд
        if (preg_match('#([[:alpha:]]+)\1{2,}#ui', $str)) {
            return $m[0];
        }

        return false;
    }

    /**
     * Проверка на мат
     * @staticvar array $pretext
     * @staticvar array $badwords
     * @staticvar array $re_trans
     * @staticvar array $trans
     * @param string $s
     * @param int $delta
     * @param string $continue
     * @return string|boolean
     */
    static function mat($s, $delta = 3, $continue = "\xe2\x80\xa6") {
        static $pretext = array(
    '[уyоo]_?        (?=[еёeхx])',
    '[вvbсc]_?       (?=[хпбмгжxpmgj])',
    '[вvbсc]_?[ъь]_? (?=[еёe])',
    'ё_?             (?=[бb])',
    '[вvb]_?[ыi]_?',
    '[зz3]_?[аa]_?',
    '[нnh]_?[аaеeиi]_?',
    '[вvb]_?[сc]_?          (?=[хпбмгжxpmgj])',
    '[оo]_?[тtбb]_?         (?=[хпбмгжxpmgj])',
    '[оo]_?[тtбb]_?[ъь]_?   (?=[еёe])',
    '[иiвvb]_?[зz3]_?       (?=[хпбмгжxpmgj])',
    '[иiвvb]_?[зz3]_?[ъь]_? (?=[еёe])',
    '[иi]_?[сc]_?           (?=[хпбмгжxpmgj])',
    '[пpдdg]_?[оo]_? (?> [бb]_?         (?=[хпбмгжxpmgj])
                           | [бb]_?  [ъь]_? (?=[еёe])
                           | [зz3]_? [аa] _?
                         )?',
    '[пp]_?[рr]_?[оoиi]_?',
    '[зz3]_?[лl]_?[оo]_?',
    '[нnh]_?[аa]_?[дdg]_?         (?=[хпбмгжxpmgj])',
    '[нnh]_?[аa]_?[дdg]_?[ъь]_?   (?=[еёe])',
    '[пp]_?[оo]_?[дdg]_?          (?=[хпбмгжxpmgj])',
    '[пp]_?[оo]_?[дdg]_?[ъь]_?    (?=[еёe])',
    '[рr]_?[аa]_?[зz3сc]_?        (?=[хпбмгжxpmgj])',
    '[рr]_?[аa]_?[зz3сc]_?[ъь]_?  (?=[еёe])',
    '[вvb]_?[оo]_?[зz3сc]_?       (?=[хпбмгжxpmgj])',
    '[вvb]_?[оo]_?[зz3сc]_?[ъь]_? (?=[еёe])',
    '[нnh]_?[еe]_?[дdg]_?[оo]_?',
    '[пp]_?[еe]_?[рr]_?[еe]_?',
    '[oо]_?[дdg]_?[нnh]_?[оo]_?',
    '[кk]_?[oо]_?[нnh]_?[оo]_?',
    '[мm]_?[уy]_?[дdg]_?[оoаa]_?',
    '[oо]_?[сc]_?[тt]_?[оo]_?',
    '[дdg]_?[уy]_?[рpr]_?[оoаa]_?',
    '[хx]_?[уy]_?[дdg]_?[оoаa]_?',
    '[мm]_?[нnh]_?[оo]_?[гg]_?[оo]_?',
    '[мm]_?[оo]_?[рpr]_?[дdg]_?[оoаa]_?',
    '[мm]_?[оo]_?[зz3]_?[гg]_?[оoаa]_?',
    '[дdg]_?[оo]_?[лl]_?[бb6]_?[оoаa]_?',
        );
        static $badwords = array(
    '(?<=[_\d]) {RE_PRETEXT}?
         [hхx]_?[уyu]_?[йiеeёяюju]     #хуй, хуя, хую, хуем, хуёвый
         #исключения:
         (?<! _hue(?=_)    #HUE    -- цветовая палитра
            | _hue(?=so_)  #hueso  -- испанское слово
            | _хуе(?=дин)  #Хуедин -- город в Румынии
         )',
    '(?<=[_\d]) {RE_PRETEXT}?
         [пp]_?[иi]_?[зz3]_?[дd]_?[:vowel:]',
    '(?<=[_\d]) {RE_PRETEXT}?
         [eеё]_? (?<!не[её]_) [бb6]_?(?: [уyиi]_                       #ебу, еби
                                       | [ыиiоoaаеeёуy]_?[:consonant:] #ебут, ебать, ебись, ебёт, поеботина, выебываться, ёбарь
                                       | [лl][оoаaыиi]                 #ебло, ебла, ебливая, еблись, еблысь
                                       | [нn]_?[уy]                    #ёбнул, ёбнутый
                                       | [кk]_?[аa]                    #взъёбка
                                      )',
    '(?<=[_\d]) {RE_PRETEXT}
         (?<=[^_\d][^_\d]|[^_\d]_[^_\d]_) [eеё]_?[бb6] (?:_|_?[аa]_?[^_\d])',
    '(?<=[_\d]) {RE_PRETEXT}?
         [бb6]_?[лl]_?(?:я|ya)(?: _       #бля
                                | _?[тд]  #блять, бляди
                              )',
    '(?<=[_\d]) [пp]_?[иieе]_?[дdg]_?[eеaаoо]_?[rpр]',
    '(?<=[_\d]) [мm]_?[уy]_?[дdg]_?[аa]',
    '(?<=[_\d]) [zж]_?h?_?[оo]_?[pп]_?[aаyуыiеeoо]',
    '(?<=[_\d]) [мm]_?[аa]_?[нnh]_?[дdg]_?[aаyуыiеeoо](?<! манда(?=[лн])|манде(?=ль ))',
    '(?<=[_\d]) [гg]_?[оo]_?[вvb]_?[нnh]_?[оoаaяеeyу]',
    '(?<=[_\d]) f_?u_?[cс]_?k',
        );
        static $re_trans = array(
    '_' => '\x20',
    '[:vowel:]' => '[аеиоуыэюяёaeioyu]',
    '[:consonant:]' => '[^аеиоуыэюяёaeioyu\x20\d]',
        );
        $re_badwords = str_replace('{RE_PRETEXT}', '(?>' . implode('|', $pretext) . ')', '~' . implode('|', $badwords) . '~sxu');
        $re_badwords = strtr($re_badwords, $re_trans);
        $s = self::strip_tags_smart($s, null, true, array('comment', 'style', 'map', 'frameset', 'object', 'applet'));
        $s = self::utf8_html_entity_decode($s, $is_htmlspecialchars = true);
        $s = self::utf8_convert_case($s, CASE_LOWER);
        static $trans = array(
    "\xc2\xad" => '',
    "\xcc\x81" => '',
    '/\\' => 'л',
    '/|' => 'л',
    "\xd0\xb5\xd0\xb5" => "\xd0\xb5\xd1\x91",
        );
        $s = strtr($s, $trans);
        preg_match_all('/(?> \xd0[\xb0-\xbf]|\xd1[\x80-\x8f\x91]  #[а-я]
                      |  [a-z\d]+
                      )+
                    /sx', $s, $m);
        $s = ' ' . implode(' ', $m[0]) . ' ';
        $s = preg_replace('/(  [\xd0\xd1][\x80-\xbf]  #оптимизированное [а-я]
                         | [a-z\d]
                         ) \\1+
                       /sx', '$1', $s);
        if (preg_match($re_badwords, $s, $m, PREG_OFFSET_CAPTURE)) {
            list($word, $offset) = $m[0];
            $s1 = substr($s, 0, $offset);
            $s2 = substr($s, $offset + strlen($word));
            $delta = intval($delta);
            if ($delta < 1 || $delta > 10)
                $delta = 3;
            preg_match('/  (?> \x20 (?>[\xd0\xd1][\x80-\xbf]|[a-z\d]+)+ ){1,' . $delta . '}
                       \x20?
                    $/sx', $s1, $m1);
            preg_match('/^ (?>[\xd0\xd1][\x80-\xbf]|[a-z\d]+)*  #окончание
                       \x20?
                       (?> (?>[\xd0\xd1][\x80-\xbf]|[a-z\d]+)+ \x20 ){1,' . $delta . '}
                    /sx', $s2, $m2);
            $fragment = (ltrim(@$m1[0]) !== ltrim($s1) ? $continue : '') .
                    trim(@$m1[0] . '[' . trim($word) . ']' . @$m2[0]) .
                    (rtrim(@$m2[0]) !== rtrim($s2) ? $continue : '');
            return $fragment;
        }
        return false;
    }

    static function utf8_html_entity_decode($s, $is_htmlspecialchars = false) {
        if (strlen($s) < 4 || ($pos = strpos($s, '&') === false) || strpos($s, ';', $pos) === false)
            return $s;
        $table = array(
            '&nbsp;' => "\xc2\xa0",
            '&iexcl;' => "\xc2\xa1",
            '&cent;' => "\xc2\xa2",
            '&pound;' => "\xc2\xa3",
            '&curren;' => "\xc2\xa4",
            '&yen;' => "\xc2\xa5",
            '&brvbar;' => "\xc2\xa6",
            '&sect;' => "\xc2\xa7",
            '&uml;' => "\xc2\xa8",
            '&copy;' => "\xc2\xa9",
            '&ordf;' => "\xc2\xaa",
            '&laquo;' => "\xc2\xab",
            '&not;' => "\xc2\xac",
            '&shy;' => "\xc2\xad",
            '&reg;' => "\xc2\xae",
            '&macr;' => "\xc2\xaf",
            '&deg;' => "\xc2\xb0",
            '&plusmn;' => "\xc2\xb1",
            '&sup2;' => "\xc2\xb2",
            '&sup3;' => "\xc2\xb3",
            '&acute;' => "\xc2\xb4",
            '&micro;' => "\xc2\xb5",
            '&para;' => "\xc2\xb6",
            '&middot;' => "\xc2\xb7",
            '&cedil;' => "\xc2\xb8",
            '&sup1;' => "\xc2\xb9",
            '&ordm;' => "\xc2\xba",
            '&raquo;' => "\xc2\xbb",
            '&frac14;' => "\xc2\xbc",
            '&frac12;' => "\xc2\xbd",
            '&frac34;' => "\xc2\xbe",
            '&iquest;' => "\xc2\xbf",
            '&Agrave;' => "\xc3\x80",
            '&Aacute;' => "\xc3\x81",
            '&Acirc;' => "\xc3\x82",
            '&Atilde;' => "\xc3\x83",
            '&Auml;' => "\xc3\x84",
            '&Aring;' => "\xc3\x85",
            '&AElig;' => "\xc3\x86",
            '&Ccedil;' => "\xc3\x87",
            '&Egrave;' => "\xc3\x88",
            '&Eacute;' => "\xc3\x89",
            '&Ecirc;' => "\xc3\x8a",
            '&Euml;' => "\xc3\x8b",
            '&Igrave;' => "\xc3\x8c",
            '&Iacute;' => "\xc3\x8d",
            '&Icirc;' => "\xc3\x8e",
            '&Iuml;' => "\xc3\x8f",
            '&ETH;' => "\xc3\x90",
            '&Ntilde;' => "\xc3\x91",
            '&Ograve;' => "\xc3\x92",
            '&Oacute;' => "\xc3\x93",
            '&Ocirc;' => "\xc3\x94",
            '&Otilde;' => "\xc3\x95",
            '&Ouml;' => "\xc3\x96",
            '&times;' => "\xc3\x97",
            '&Oslash;' => "\xc3\x98",
            '&Ugrave;' => "\xc3\x99",
            '&Uacute;' => "\xc3\x9a",
            '&Ucirc;' => "\xc3\x9b",
            '&Uuml;' => "\xc3\x9c",
            '&Yacute;' => "\xc3\x9d",
            '&THORN;' => "\xc3\x9e",
            '&szlig;' => "\xc3\x9f",
            '&agrave;' => "\xc3\xa0",
            '&aacute;' => "\xc3\xa1",
            '&acirc;' => "\xc3\xa2",
            '&atilde;' => "\xc3\xa3",
            '&auml;' => "\xc3\xa4",
            '&aring;' => "\xc3\xa5",
            '&aelig;' => "\xc3\xa6",
            '&ccedil;' => "\xc3\xa7",
            '&egrave;' => "\xc3\xa8",
            '&eacute;' => "\xc3\xa9",
            '&ecirc;' => "\xc3\xaa",
            '&euml;' => "\xc3\xab",
            '&igrave;' => "\xc3\xac",
            '&iacute;' => "\xc3\xad",
            '&icirc;' => "\xc3\xae",
            '&iuml;' => "\xc3\xaf",
            '&eth;' => "\xc3\xb0",
            '&ntilde;' => "\xc3\xb1",
            '&ograve;' => "\xc3\xb2",
            '&oacute;' => "\xc3\xb3",
            '&ocirc;' => "\xc3\xb4",
            '&otilde;' => "\xc3\xb5",
            '&ouml;' => "\xc3\xb6",
            '&divide;' => "\xc3\xb7",
            '&oslash;' => "\xc3\xb8",
            '&ugrave;' => "\xc3\xb9",
            '&uacute;' => "\xc3\xba",
            '&ucirc;' => "\xc3\xbb",
            '&uuml;' => "\xc3\xbc",
            '&yacute;' => "\xc3\xbd",
            '&thorn;' => "\xc3\xbe",
            '&yuml;' => "\xc3\xbf",
            '&fnof;' => "\xc6\x92",
            '&Alpha;' => "\xce\x91",
            '&Beta;' => "\xce\x92",
            '&Gamma;' => "\xce\x93",
            '&Delta;' => "\xce\x94",
            '&Epsilon;' => "\xce\x95",
            '&Zeta;' => "\xce\x96",
            '&Eta;' => "\xce\x97",
            '&Theta;' => "\xce\x98",
            '&Iota;' => "\xce\x99",
            '&Kappa;' => "\xce\x9a",
            '&Lambda;' => "\xce\x9b",
            '&Mu;' => "\xce\x9c",
            '&Nu;' => "\xce\x9d",
            '&Xi;' => "\xce\x9e",
            '&Omicron;' => "\xce\x9f",
            '&Pi;' => "\xce\xa0",
            '&Rho;' => "\xce\xa1",
            '&Sigma;' => "\xce\xa3",
            '&Tau;' => "\xce\xa4",
            '&Upsilon;' => "\xce\xa5",
            '&Phi;' => "\xce\xa6",
            '&Chi;' => "\xce\xa7",
            '&Psi;' => "\xce\xa8",
            '&Omega;' => "\xce\xa9",
            '&alpha;' => "\xce\xb1",
            '&beta;' => "\xce\xb2",
            '&gamma;' => "\xce\xb3",
            '&delta;' => "\xce\xb4",
            '&epsilon;' => "\xce\xb5",
            '&zeta;' => "\xce\xb6",
            '&eta;' => "\xce\xb7",
            '&theta;' => "\xce\xb8",
            '&iota;' => "\xce\xb9",
            '&kappa;' => "\xce\xba",
            '&lambda;' => "\xce\xbb",
            '&mu;' => "\xce\xbc",
            '&nu;' => "\xce\xbd",
            '&xi;' => "\xce\xbe",
            '&omicron;' => "\xce\xbf",
            '&pi;' => "\xcf\x80",
            '&rho;' => "\xcf\x81",
            '&sigmaf;' => "\xcf\x82",
            '&sigma;' => "\xcf\x83",
            '&tau;' => "\xcf\x84",
            '&upsilon;' => "\xcf\x85",
            '&phi;' => "\xcf\x86",
            '&chi;' => "\xcf\x87",
            '&psi;' => "\xcf\x88",
            '&omega;' => "\xcf\x89",
            '&thetasym;' => "\xcf\x91",
            '&upsih;' => "\xcf\x92",
            '&piv;' => "\xcf\x96",
            '&bull;' => "\xe2\x80\xa2",
            '&hellip;' => "\xe2\x80\xa6",
            '&prime;' => "\xe2\x80\xb2",
            '&Prime;' => "\xe2\x80\xb3",
            '&oline;' => "\xe2\x80\xbe",
            '&frasl;' => "\xe2\x81\x84",
            '&weierp;' => "\xe2\x84\x98",
            '&image;' => "\xe2\x84\x91",
            '&real;' => "\xe2\x84\x9c",
            '&trade;' => "\xe2\x84\xa2",
            '&alefsym;' => "\xe2\x84\xb5",
            '&larr;' => "\xe2\x86\x90",
            '&uarr;' => "\xe2\x86\x91",
            '&rarr;' => "\xe2\x86\x92",
            '&darr;' => "\xe2\x86\x93",
            '&harr;' => "\xe2\x86\x94",
            '&crarr;' => "\xe2\x86\xb5",
            '&lArr;' => "\xe2\x87\x90",
            '&uArr;' => "\xe2\x87\x91",
            '&rArr;' => "\xe2\x87\x92",
            '&dArr;' => "\xe2\x87\x93",
            '&hArr;' => "\xe2\x87\x94",
            '&forall;' => "\xe2\x88\x80",
            '&part;' => "\xe2\x88\x82",
            '&exist;' => "\xe2\x88\x83",
            '&empty;' => "\xe2\x88\x85",
            '&nabla;' => "\xe2\x88\x87",
            '&isin;' => "\xe2\x88\x88",
            '&notin;' => "\xe2\x88\x89",
            '&ni;' => "\xe2\x88\x8b",
            '&prod;' => "\xe2\x88\x8f",
            '&sum;' => "\xe2\x88\x91",
            '&minus;' => "\xe2\x88\x92",
            '&lowast;' => "\xe2\x88\x97",
            '&radic;' => "\xe2\x88\x9a",
            '&prop;' => "\xe2\x88\x9d",
            '&infin;' => "\xe2\x88\x9e",
            '&ang;' => "\xe2\x88\xa0",
            '&and;' => "\xe2\x88\xa7",
            '&or;' => "\xe2\x88\xa8",
            '&cap;' => "\xe2\x88\xa9",
            '&cup;' => "\xe2\x88\xaa",
            '&int;' => "\xe2\x88\xab",
            '&there4;' => "\xe2\x88\xb4",
            '&sim;' => "\xe2\x88\xbc",
            '&cong;' => "\xe2\x89\x85",
            '&asymp;' => "\xe2\x89\x88",
            '&ne;' => "\xe2\x89\xa0",
            '&equiv;' => "\xe2\x89\xa1",
            '&le;' => "\xe2\x89\xa4",
            '&ge;' => "\xe2\x89\xa5",
            '&sub;' => "\xe2\x8a\x82",
            '&sup;' => "\xe2\x8a\x83",
            '&nsub;' => "\xe2\x8a\x84",
            '&sube;' => "\xe2\x8a\x86",
            '&supe;' => "\xe2\x8a\x87",
            '&oplus;' => "\xe2\x8a\x95",
            '&otimes;' => "\xe2\x8a\x97",
            '&perp;' => "\xe2\x8a\xa5",
            '&sdot;' => "\xe2\x8b\x85",
            '&lceil;' => "\xe2\x8c\x88",
            '&rceil;' => "\xe2\x8c\x89",
            '&lfloor;' => "\xe2\x8c\x8a",
            '&rfloor;' => "\xe2\x8c\x8b",
            '&lang;' => "\xe2\x8c\xa9",
            '&rang;' => "\xe2\x8c\xaa",
            '&loz;' => "\xe2\x97\x8a",
            '&spades;' => "\xe2\x99\xa0",
            '&clubs;' => "\xe2\x99\xa3",
            '&hearts;' => "\xe2\x99\xa5",
            '&diams;' => "\xe2\x99\xa6",
            '&OElig;' => "\xc5\x92",
            '&oelig;' => "\xc5\x93",
            '&Scaron;' => "\xc5\xa0",
            '&scaron;' => "\xc5\xa1",
            '&Yuml;' => "\xc5\xb8",
            '&circ;' => "\xcb\x86",
            '&tilde;' => "\xcb\x9c",
            '&ensp;' => "\xe2\x80\x82",
            '&emsp;' => "\xe2\x80\x83",
            '&thinsp;' => "\xe2\x80\x89",
            '&zwnj;' => "\xe2\x80\x8c",
            '&zwj;' => "\xe2\x80\x8d",
            '&lrm;' => "\xe2\x80\x8e",
            '&rlm;' => "\xe2\x80\x8f",
            '&ndash;' => "\xe2\x80\x93",
            '&mdash;' => "\xe2\x80\x94",
            '&lsquo;' => "\xe2\x80\x98",
            '&rsquo;' => "\xe2\x80\x99",
            '&sbquo;' => "\xe2\x80\x9a",
            '&ldquo;' => "\xe2\x80\x9c",
            '&rdquo;' => "\xe2\x80\x9d",
            '&bdquo;' => "\xe2\x80\x9e",
            '&dagger;' => "\xe2\x80\xa0",
            '&Dagger;' => "\xe2\x80\xa1",
            '&permil;' => "\xe2\x80\xb0",
            '&lsaquo;' => "\xe2\x80\xb9",
            '&rsaquo;' => "\xe2\x80\xba",
            '&euro;' => "\xe2\x82\xac",
        );
        $htmlspecialchars = array(
            '&quot;' => "\x22",
            '&amp;' => "\x26",
            '&lt;' => "\x3c",
            '&gt;' => "\x3e",
        );
        if ($is_htmlspecialchars)
            $table += $htmlspecialchars;
        preg_match_all('/&[a-zA-Z]+\d*;/s', $s, $m, null, $pos);
        foreach (array_unique($m[0]) as $entity) {
            if (array_key_exists($entity, $table))
                $s = str_replace($entity, $table[$entity], $s);
        }
        if (($pos = strpos($s, '&#')) !== false) {
            $htmlspecialchars_flip = array_flip($htmlspecialchars);
            $s = preg_replace(
                    '/&#((x)[\da-fA-F]{2,4}|\d{1,4});/se', '(array_key_exists($a = pack("C", $d = ("$2") ? hexdec("$1") : "$1"), $htmlspecialchars_flip) && ! $is_htmlspecialchars) ?
             $htmlspecialchars_flip[$a] :
             iconv("UCS-2BE", "UTF-8", pack("n", $d))', $s, - 1, $pos);
        }
        return $s;
    }

    static function utf8_convert_case($s, $mode) {
        static $trans = array(
    "\x41" => "\x61",
    "\x42" => "\x62",
    "\x43" => "\x63",
    "\x44" => "\x64",
    "\x45" => "\x65",
    "\x46" => "\x66",
    "\x47" => "\x67",
    "\x48" => "\x68",
    "\x49" => "\x69",
    "\x4a" => "\x6a",
    "\x4b" => "\x6b",
    "\x4c" => "\x6c",
    "\x4d" => "\x6d",
    "\x4e" => "\x6e",
    "\x4f" => "\x6f",
    "\x50" => "\x70",
    "\x51" => "\x71",
    "\x52" => "\x72",
    "\x53" => "\x73",
    "\x54" => "\x74",
    "\x55" => "\x75",
    "\x57" => "\x77",
    "\x56" => "\x76",
    "\x58" => "\x78",
    "\x59" => "\x79",
    "\x5a" => "\x7a",
    "\xd0\x81" => "\xd1\x91",
    "\xd0\x90" => "\xd0\xb0",
    "\xd0\x91" => "\xd0\xb1",
    "\xd0\x92" => "\xd0\xb2",
    "\xd0\x93" => "\xd0\xb3",
    "\xd0\x94" => "\xd0\xb4",
    "\xd0\x95" => "\xd0\xb5",
    "\xd0\x96" => "\xd0\xb6",
    "\xd0\x97" => "\xd0\xb7",
    "\xd0\x98" => "\xd0\xb8",
    "\xd0\x99" => "\xd0\xb9",
    "\xd0\x9a" => "\xd0\xba",
    "\xd0\x9b" => "\xd0\xbb",
    "\xd0\x9c" => "\xd0\xbc",
    "\xd0\x9d" => "\xd0\xbd",
    "\xd0\x9e" => "\xd0\xbe",
    "\xd0\x9f" => "\xd0\xbf",
    "\xd0\xa0" => "\xd1\x80",
    "\xd0\xa1" => "\xd1\x81",
    "\xd0\xa2" => "\xd1\x82",
    "\xd0\xa3" => "\xd1\x83",
    "\xd0\xa4" => "\xd1\x84",
    "\xd0\xa5" => "\xd1\x85",
    "\xd0\xa6" => "\xd1\x86",
    "\xd0\xa7" => "\xd1\x87",
    "\xd0\xa8" => "\xd1\x88",
    "\xd0\xa9" => "\xd1\x89",
    "\xd0\xaa" => "\xd1\x8a",
    "\xd0\xab" => "\xd1\x8b",
    "\xd0\xac" => "\xd1\x8c",
    "\xd0\xad" => "\xd1\x8d",
    "\xd0\xae" => "\xd1\x8e",
    "\xd0\xaf" => "\xd1\x8f",
    "\xd2\x96" => "\xd2\x97",
    "\xd2\xa2" => "\xd2\xa3",
    "\xd2\xae" => "\xd2\xaf",
    "\xd2\xba" => "\xd2\xbb",
    "\xd3\x98" => "\xd3\x99",
    "\xd3\xa8" => "\xd3\xa9",
    "\xd2\x90" => "\xd2\x91",
    "\xd0\x84" => "\xd1\x94",
    "\xd0\x86" => "\xd1\x96",
    "\xd0\x87" => "\xd1\x97",
    "\xd0\x8e" => "\xd1\x9e",
    "\xc3\x84" => "\xc3\xa4",
    "\xc3\x87" => "\xc3\xa7",
    "\xc3\x91" => "\xc3\xb1",
    "\xc3\x96" => "\xc3\xb6",
    "\xc3\x9c" => "\xc3\xbc",
    "\xc4\x9e" => "\xc4\x9f",
    "\xc4\xb0" => "\xc4\xb1",
    "\xc5\x9e" => "\xc5\x9f",
    "\xc4\x8c" => "\xc4\x8d",
    "\xc4\x86" => "\xc4\x87",
    "\xc4\x90" => "\xc4\x91",
    "\xc5\xa0" => "\xc5\xa1",
    "\xc5\xbd" => "\xc5\xbe",
    "\xc3\x80" => "\xc3\xa0",
    "\xc3\x82" => "\xc3\xa2",
    "\xc3\x86" => "\xc3\xa6",
    "\xc3\x88" => "\xc3\xa8",
    "\xc3\x89" => "\xc3\xa9",
    "\xc3\x8a" => "\xc3\xaa",
    "\xc3\x8b" => "\xc3\xab",
    "\xc3\x8e" => "\xc3\xae",
    "\xc3\x8f" => "\xc3\xaf",
    "\xc3\x94" => "\xc3\xb4",
    "\xc5\x92" => "\xc5\x93",
    "\xc3\x99" => "\xc3\xb9",
    "\xc3\x9b" => "\xc3\xbb",
    "\xc5\xb8" => "\xc3\xbf",
        );
        if ($mode == CASE_UPPER) {
            if (function_exists('mb_strtoupper'))
                return mb_strtoupper($s, 'utf-8');
            if (preg_match('/^[\x00-\x7e]*$/', $s))
                return strtoupper($s);
            strtr($s, array_flip($trans));
        } elseif ($mode == CASE_LOWER) {
            if (function_exists('mb_strtolower'))
                return mb_strtolower($s, 'utf-8');
            if (preg_match('/^[\x00-\x7e]*$/', $s))
                return strtolower($s);
            strtr($s, $trans);
        } else {
            trigger_error('Parameter 2 should be a constant of CASE_LOWER or CASE_UPPER!', E_USER_WARNING);
            return $s;
        }
        return $s;
    }


    static function strip_tags_smart($s, array $allowable_tags = null, $is_format_spaces = false, array $pair_tags = array('script', 'style', 'map', 'iframe', 'frameset', 'object', 'applet', 'comment', 'button'), array $para_tags = array('p', 'td', 'th', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'form', 'title')
    ) {
        static $_callback_type = false;
        static $_allowable_tags = array();
        static $_para_tags = array();
        static $re_attrs_fast_safe = '(?> (?>[\x20\r\n\t]+|\xc2\xa0)+  #пробельные символы (д.б. обязательно)
                                       (?>
                                         #правильные атрибуты
                                                                        [^>"\']+
                                         | (?<=[\=\x20\r\n\t]|\xc2\xa0) "[^"]*"
                                         | (?<=[\=\x20\r\n\t]|\xc2\xa0) \'[^\']*\'
                                         #разбитые атрибуты
                                         |                              [^>]+
                                       )*
                                   )?';
        if (is_array($s) && $_callback_type === 'strip_tags') {
            $tag = strtolower($s[1]);
            if ($_allowable_tags &&
                    (array_key_exists($tag, $_allowable_tags) || array_key_exists('<' . trim(strtolower($s[0]), '< />') . '>', $_allowable_tags))
            )
                return $s[0];
            if ($tag == 'br')
                return "\r\n";
            if ($_para_tags && array_key_exists($tag, $_para_tags))
                return "\r\n\r\n";
            return '';
        }
        if (($pos = strpos($s, '<') === false) || strpos($s, '>', $pos) === false) {
            return $s;
        }
        $re_tags = '/<[\/\!]? ([a-zA-Z][a-zA-Z\d]* (?>\:[a-zA-Z][a-zA-Z\d]*)?)' . $re_attrs_fast_safe . '\/?>/sx';
        $patterns = array(
            '/<([\?\%]) .*? \\1>/sx',
            '/<\!\[CDATA\[ .*? \]\]>/sx',
            '/<\!--.*?-->/s',
            '/<\! (?>--)?
              \[
              (?> [^\]"\']+ | "[^"]*" | \'[^\']*\' )*
              \]
              (?>--)?
         >/sx',
        );
        if ($pair_tags) {
            foreach ($pair_tags as $k => $v)
                $pair_tags[$k] = preg_quote($v, '/');
            $patterns[] = '/<((?i:' . implode('|', $pair_tags) . '))' . $re_attrs_fast_safe . '> .*? <\/(?i:\\1)' . $re_attrs_fast_safe . '>/sx';
        }
        $i = 0;
        $max = 99;
        while ($i < $max) {
            $s2 = preg_replace($patterns, '', $s);
            if ($i == 0) {
                $is_html = ($s2 != $s || preg_match($re_tags, $s2));
                if ($is_html) {
                    $s2 = strtr($s2, "\x09\x0a\x0c\x0d", '    ');
                    if ($allowable_tags)
                        $_allowable_tags = array_flip($allowable_tags);
                    if ($para_tags)
                        $_para_tags = array_flip($para_tags);
                }
            }
            if ($is_html) {
                $_callback_type = 'strip_tags';
                $s2 = preg_replace_callback($re_tags, array(__CLASS__, 'strip_tags_smart'), $s2);
                $_callback_type = false;
            }
            if ($s === $s2)
                break;
            $s = $s2;
            $i++;
        }
        if ($i >= $max)
            $s = strip_tags($s);
        if ($is_format_spaces || $is_html) {
            $s = preg_replace('/\x20\x20+/s', ' ', trim($s));
            $s = str_replace(array("\r\n\x20", "\x20\r\n"), "\r\n", $s);
            $s = preg_replace('/\r\n[\r\n]+/s', "\r\n\r\n", $s);
        }
        return $s;
    }

}