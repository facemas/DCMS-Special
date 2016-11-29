<?php

class url {

    protected
            $_params = array(),
            $_scheme,
            $_host,
            $_port,
            $_user,
            $_pass,
            $_path = "",
            $_fragment;

    /**
     * @param string $url
     * @param array $params
     */
    function __construct($url = null, $params = array()) {
        if (is_null($url)) {
            $url = $_SERVER['REQUEST_URI'];
        }
        $this->_parseUrl($url);
        $this->_params = array_merge($this->_params, $params);
    }

    private function _parseUrl($url) {
        $components = array('scheme', 'host', 'port', 'user', 'pass', 'path', 'fragment');
        $parsed = parse_url($url);
        foreach ($components AS $component) {
            $prop_name = '_' . $component;
            if (array_key_exists($component, $parsed)) {
                $this->$prop_name = $parsed[$component];
            } else {
                $this->$prop_name = null;
            }
        }

        if (array_key_exists('query', $parsed)) {
            $query_parts = preg_split('/&(amp;)?/', $parsed['query']);
            for ($i = 0; $i < count($query_parts); $i++) {
                if (!$query_parts[$i])
                    continue;
                $query_part = explode('=', $query_parts[$i], 2);
                $this->setParam(urldecode($query_part[0]), isset($query_part[1]) ? urldecode($query_part[1]) : null);
            }
        }
    }

    public function __toString() {
        return htmlspecialchars($this->getUrl());
    }

    public function getParam($name, $default = null) {
        if (!array_key_exists($name, $this->_params)) {
            return $default;
        }
        return $this->_params[$name];
    }

    public function setParam($name, $value) {
        $this->_params[$name] = $value;
        return $this;
    }

    public function removeParam($name) {
        unset($this->_params[$name]);
        return $this;
    }

    public function setPath($path) {
        $this->_path = $path;
        return $this;
    }

    /**
     * Возвращает url как строку
     * @return string
     */
    public function getUrl() {
        if (!$this->_host && ($this->_port || $this->_scheme || $this->_user)) {
            $this->_host = $_SERVER['HTTP_HOST'];
        }

        $url = '';
        if ($this->_scheme) {
            $url .= $this->_scheme . ':';
        }

        if ($this->_host) {
            $url .= '//';
        }

        if ($this->_user) {
            $url .= $this->_user;
            if ($this->_pass) {
                $url .= $this->_pass;
            }
            $url .= '@';
        }

        if ($this->_host) {
            $url .= $this->_host;
        }

        if ($this->_port) {
            $url .= ':' . $this->_port;
        }

        $url .= $this->_path;

        if ($this->_params) {
            $params_query = array();
            foreach ($this->_params AS $key => $value) {
                $params_query[] = urlencode($key) . (is_null($value) ? '' : '=' . urlencode($value));
            }
            $url .= '?' . implode('&', $params_query);
        }

        if ($this->_fragment) {
            $url .= '#' . $this->_fragment;
        }

        return $url;
    }

    /**
     * true, если ссылка внутри сайта
     * @return boolean
     */
    public function isInternalLink() {
        if (!$this->_host || $this->_host === $_SERVER['HTTP_HOST']) {
            return true;
        }
        return false;
    }

}
