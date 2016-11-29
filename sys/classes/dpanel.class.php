<?php

/**
 * Управление доступом в админку
 */
abstract class dpanel {

    /**
     * Проверка страницы капчей
     */
    static function check_access() {
        if (self::is_access()) {
            self::access();
        } else {
            header("Location: /dpanel/login.php?return=" . URL . '&' . SID);
            exit;
        }
    }

    /**
     * Проверяет, была ли введена капча
     * @return bool
     */
    static function is_access() {
        return cache_dpanel_access::get(self::key());
    }

    /**
     * Разрешает доступ
     */
    static function access() {
        cache_dpanel_access::set(self::key(), true, 3600);
    }

    /**
     * Удаляет разрешение на доступ
     */
    static function access_delete() {
        cache_dpanel_access::set(self::key(), false, 1);
    }

    # Ключ идентификации пользователя, для которого разрешается доступ

    static function key() {
        global $dcms, $user;
        return 'dpanel.access.' . $user->id . '.' . (string) $dcms->ip_long . '.' . (string) $dcms->browser;
    }

}
