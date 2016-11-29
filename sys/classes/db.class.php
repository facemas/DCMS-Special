<?php

/**
 * Class ExceptionPdoNotExists
 */
class ExceptionPdoNotExists extends Exception {
    
}

/*
  Класс для подключения к БД
  Можно использовать в любом месте движка
  $db = DB::me();
 */

class DB {

    static protected
            $pdo,
            $host,
            $user,
            $password,
            $db_name;

    /**
     * @return PDO
     * @throws ExceptionPdoNotExists
     * @throws Exception
     */
    static public function me() {
        if (!class_exists('pdo') || array_search('mysql', PDO::getAvailableDrivers()) === false)
            throw new ExceptionPdoNotExists("Отсутствует драйвер PDO");

        $args = func_get_args();
        if (count($args) == 4) {
            self::$host = $args[0];
            self::$db_name = $args[1];
            self::$user = $args[2];
            self::$password = $args[3];
        }

        if (is_null(self::$pdo)) {
            if (!self::$db_name) {
                throw new Exception('Укажите параметры соединения');
            }

            self::$pdo = new PDO('mysql:host=' . self::$host . ';dbname=' . self::$db_name, self::$user, self::$password);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo->query("SET NAMES utf8;");
        }
        return self::$pdo;
    }

    static public function isConnected() {
        return !is_null(self::$pdo);
    }

    protected function __construct() {
        
    }

}
