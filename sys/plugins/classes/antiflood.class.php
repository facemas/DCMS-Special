<?php

/**
 * Модуль антифлуда, построенный на токенах, которые предполагается использовать в скрытых полях форм.
 * Должен предотвращать добавление сообщений при повторной отправке формы
 * Class antiflood
 */
abstract class antiflood {

    const SESS_KEY = 'antiflood_tokens';

    /**
     * Максимальное количество токенов на один модуль
     */
    const MAX_TOKENS = 5;

    static function getToken($module = 'common') {
        self::prepareTokens($module);
        self::clearTokens($module);

        $token = passgen();
        $_SESSION[self::SESS_KEY][$module][] = $token;
        return $token;
    }

    static function useToken($token, $module = 'common') {
        self::prepareTokens($module);
        if (!in_array($token, $_SESSION[self::SESS_KEY][$module]))
            return false;
        unset($_SESSION[self::SESS_KEY][$module]);
        return true;
    }

    private static function prepareTokens($module) {
        if (!isset($_SESSION[self::SESS_KEY]))
            $_SESSION[self::SESS_KEY] = array();

        if (!isset($_SESSION[self::SESS_KEY][$module]))
            $_SESSION[self::SESS_KEY][$module] = array();
    }

    private static function clearTokens($module) {
        array_splice($_SESSION[self::SESS_KEY][$module], 0, count($_SESSION[self::SESS_KEY][$module]) - self::MAX_TOKENS);
    }

}
