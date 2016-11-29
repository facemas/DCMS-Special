<?php

/**
 * Запрос с клиента
 * Class api_request
 */
class api_request {

    public $module;
    public $method;
    public $data;

    function __construct($params) {
        foreach ($params AS $key => $value) {
            if (!property_exists($this, $key))
                throw new Exception("Property $key is not exists");
            $this->$key = $value;
        }

        if (!$this->module)
            throw new Exception('Property "module" is not defined');
        if (!$this->method)
            throw new Exception('Property "method" is not defined');
    }

}
