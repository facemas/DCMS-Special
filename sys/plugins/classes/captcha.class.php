<?php

/**
 * Работа с данными для картинки с проверочным кодом (капчей)
 */
abstract class captcha {
    
    /**
     * генерация проверочного кода и возврат сессии
     * @return string
     */
    static function gen()
    {
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= mt_rand(0, 9);
        }
        $sess = passgen();
        $_SESSION['captcha_session'][$sess] = (string)$code;
        return $sess;
    }
    
    /**
     * получение кода по сессии (для отображения капчи)
     * @param string $sess
     * @return string
     */
    static function getCode($sess)
    {
        return (!empty($_SESSION['captcha_session'][$sess]))?$_SESSION['captcha_session'][$sess]:false;
    }
    
    /**
     * проверка, введенного пользователем, кода по сессии и последующее удаление сессии
     * @param string $code введенный код
     * @param string $sess сессия
     * @return boolean
     */
    static function check($code, $sess)
    {
        if (empty($_SESSION['captcha_session'][$sess]))return false;
        $return = (bool)($_SESSION['captcha_session'][$sess] === (string)$code);

        unset($_SESSION['captcha_session'][$sess]);
        return $return;
    }
}
