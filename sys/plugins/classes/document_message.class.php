<?php

class document_message {

    public $text, $isError;

    function __construct($text, $isError = false) {
        $this->text = $text;
        $this->isError = $isError;
    }

}
