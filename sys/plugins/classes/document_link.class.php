<?php

class document_link {

    public $url, $name, $selected, $icon;

    function __construct($name, $url, $selected = false, $icon = false) {
        $this->name = $name;
        $this->icon = $icon;
        $this->url = (string) $url;
        $this->selected = $selected;
    }

}

