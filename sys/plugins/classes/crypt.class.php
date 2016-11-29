<?php

/**
 * Шифрование и хэширование данных
 */
abstract class crypt {

    /**
     * Получаем вектор шифрования
     * @return boolean
     */
    static function getIV() {
        if (!function_exists('mcrypt_module_open'))
            return false;
        static $iv = false;

        if (!$iv)
            $iv = @file_get_contents(H . '/sys/ini/iv.dat');

        if (!$iv) {
            $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
            if (file_put_contents(H . '/sys/ini/iv.dat', $iv) === false)
                die('Не удалось сохранить sys/ini/iv.dat');
            @chmod(H . '/sys/ini/iv.dat', filesystem::getChmodToRead());
        }

        return $iv;
    }

    /**
     * делаем хэш пароля с наложением соли (покажем большой куй всем сервисам с md5 базами)
     * @param string $pass Исходный пароль
     * @param string $salt Соль
     * @return string Хэш пароля
     */
    static function hash($pass, $salt) {
        return md5($salt . md5((string) $pass) . md5($salt) . $salt);
    }

    /**
     * Шифрование данных указанным ключем
     * @param string $str Исходная строка
     * @param string $key Ключ
     * @return string Шифрованные данные
     */
    static function encrypt($str, $key) {
        if ($iv = self::getIV()) {
            $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
            $ks = @mcrypt_enc_get_key_size($td);
            $key = substr(md5($key), 0, $ks);
            @mcrypt_generic_init($td, $key, $iv);
            $str = @mcrypt_generic($td, $str);
            @mcrypt_generic_deinit($td);
            @mcrypt_module_close($td);
        }

        return base64_encode(base64_encode($str));
    }

    /**
     * Расшифровка данных указанным ключем
     * @param string $str Шифрованная строка
     * @param string $key Ключ
     * @return string Исходные данные
     */
    static function decrypt($str, $key) {
        $str = base64_decode(base64_decode($str));
        if ($iv = self::getIV()) {
            $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
            $ks = @mcrypt_enc_get_key_size($td);
            $key = substr(md5($key), 0, $ks);
            @mcrypt_generic_init($td, $key, $iv);
            $str = @mdecrypt_generic($td, $str);
            @mcrypt_generic_deinit($td);
            @mcrypt_module_close($td);
        }
        return $str;
    }

}
