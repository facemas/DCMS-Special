<?php

/**
 * Все API контроллеры должны быть реализацией данного интерфейса.
 * Проверка интерфейса позволит избежать выполнения произвольного класса.
 * Interface api_controller
 */
interface api_controller {
    
}

/**
 * Исключение ApiController`а
 * Class ApiException
 */
class ApiException extends Exception {

    public $message;
    public $request;

    /**
     * @param mixed $request
     * @param string $message
     */
    function __construct($request, $message = 'Undefined Error') {
        $this->request = $request;
        $this->message = $message;
    }

}

/**
 * Исключение, указывающее на необходимость авторизации
 * Class AuthRequiredException
 */
class ApiAuthRequiredException extends ApiException {

    public $require_auth = true;

    function __construct($request) {
        parent::__construct($request, __('Необходима авторизация'));
    }

}
