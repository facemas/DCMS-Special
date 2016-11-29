<?php

class current_user {

    static protected $_instance = null;

    protected function __construct() {
        
    }

    static public function getInstance($id = false) {
        if (is_null(self::$_instance))
            self::$_instance = new user($id);
        return self::$_instance;
    }

}
