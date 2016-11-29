<?php

/**
 * Генератор форм
 */
class form extends ui {

    /**
     * Создание формы
     * @param string|url $url Путь (атрибут action в форме)
     * @param boolean $post true-отправлять post`ом, false - get`ом
     * @param string $atr Дополнительные атрибуты
     */
    public function __construct($url = '', $post = true, $atr = false) {
        parent::__construct();
        $this->_tpl_file = 'input.form.tpl';

        $this->_data['el'] = array();
        $this->_data['atr'] = (string) $atr;

        $this->set_url($url);
        $this->set_method($post ? 'post' : 'get');
    }

    /**
     * URL для обновления формы
     * @param string $url
     */
    function refresh_url($url) {
        $this->_data['refresh_url'] = $url;
    }

    /**
     * Вставка HTML блока
     * @param string $html
     * @param boolean $br
     */
    function html($html, $br = false) {
        $this->_data['el'][] = array(
            'type' => 'text',
            'br' => (bool) $br,
            'value' => $html
        );
    }

    /**
     * Вставка FIELS блока 
     * Этот блок объединяет поля
     * @param string $start_fiels пишем название правила
     * @param boolean $close_fiels
     */
    function fiels($start_fiels = 'fiels', $close_fiels = false) {
        $this->_data['el'][] = array(
            'start_fiels' => $start_fiels,
            'close_fiels' => (bool) $close_fiels
        );
    }

    function block($block) {
        $this->_data['el'][] = array(
            'type' => 'html',
            'block' => $block
        );
    }

    /**
     * Вставка текстового блока, который будет обработан BBCODE
     * @param string $bbcode
     * @param boolean $br
     */
    function bbcode($bbcode, $br = true) {
        $this->html(text::toOutput($bbcode), $br);
    }

    /**
     * Чекбокс
     * @param string $name аттрибут name
     * @param string $title текст к чекбоксу
     * @param boolean $checked значение, установлена ли галочка
     * @param boolean $br перенос строки
     * @param string $value аттрибут value
     */
    function checkbox($name, $title, $checked = false, $br = true, $value = '1') {
        $this->_data['el'][] = array(
            'type' => 'checkbox',
            'br' => (bool) $br,
            'info' => array(
                'name' => text::toValue($name),
                'checked' => (bool) $checked,
                'value' => text::toValue($value),
                'text' => text::toValue($title)
            )
        );
    }

    /**
     * Поле "select"
     * @param string $name
     * @param string $title
     * @param array $options
     * @param boolean $br
     */
    function select($name, $title, $options, $br = true) {
        $this->_data['el'][] = array(
            'type' => 'select',
            'title' => text::toValue($title),
            'br' => (bool) $br,
            'info' => array(
                'name' => text::toValue($name),
                'options' => (array) $options
            )
        );
    }

    /**
     * Кнопка
     * @param string $text Отображаемое название кнопки
     * @param string $name аттрибут name
     * @param boolean $br перенос
     * @param string $class Класс кнопки, необходимо, что бы комбинировать хоть
     */
    function button($text, $name = '', $br = true, $class = 'tiny ui blue button', $icon = false) {
        $this->input($name, '', $text, 'submit', $br, false, false, false, $class, $icon);
    }

    /**
     * Поде для выбора файла
     * @param string $name аттрибут name
     * @param string $title Заголовок к полю выбора файла
     * @param boolean $br перенос строки
     */
    function file($name, $title, $br = false) {
        $this->input($name, $title, false, 'file', $br);
    }

    function fileMultiple($name, $title, $br = true) {
        $input = array();
        $this->set_is_files();

        $input['type'] = 'file';
        $input['title'] = text::toOutput($title);
        $input['br'] = (bool) $br;

        $info = array();
        $info['name'] = text::toValue($name);
        $info['multiple'] = true;

        $input['info'] = $info;
        $this->_data['el'][] = $input;
        return true;
    }

    /**
     * Капча
     * @param boolean $br перенос строки
     */
    function captcha($br = true) {
        $this->_data['el'][] = array('type' => 'captcha', 'br' => $br, 'session' => captcha::gen());
    }

    /**
     * Поле ввода пароля
     * @param string $name аттрибут name
     * @param string $title Заголовок к полю ввода
     * @param string $value введенное значение в поле
     * @param boolean $br перенос строки
     * @param bool|int $size ширина поля ввода в символах
     */
    function password($name, $title, $value = '', $br = false, $size = false) {
        $this->input($name, $title, $value, 'password', $br, $size);
    }

    /**
     * Текстовое поле ввода
     * @param string $name аттрибут name
     * @param string $title Заголовок поля ввода
     * @param string $value значение в поле ввода
     * @param boolean $br перенос строки
     * @param bool|int $size ширина поля ввода в символах
     * @param boolean $disabled запретить изменение
     */
    function text($name, $title, $value = '', $br = false, $size = false, $disabled = false) {
        $this->input($name, $title, $value, 'text', $br, $size, $disabled);
    }

    /**
     * Скрытое поле формы
     * @param string $name аттрибут name
     * @param string $value значение
     */
    function hidden($name, $value) {
        $this->input($name, '', $value, 'hidden', false);
    }

    /**
     * Поле ввода для сообщения
     * @param string $name аттрибут name
     * @param string $title заголовок поля ввода
     * @param string $value введенный текст
     * @param bool $submit_ctrl_enter отправка формы по Ctrl + Enter
     * @param boolean $br перенос
     * @param boolean $disabled запретить изменение
     */
    function textarea($name, $title, $value = '', $submit_ctrl_enter = false, $br = false, $disabled = false) {
        $this->_data['el'][] = array(
            'type' => 'textarea',
            'title' => text::toOutput($title),
            'br' => (bool) $br,
            'submit_ctrl_enter' => (bool) $submit_ctrl_enter,
            'info' => array(
                'name' => text::toValue($name),
                'value' => $value, // фильтрация в шаблоне
                'disabled' => (bool) $disabled
        ));
    }

    /**
     * Добавление input`a
     * @param string $name аттрибут name
     * @param string $title заголовок
     * @param string $value значение по-умолчанию
     * @param string $type тип (аттрибут type)
     * @param boolean $br вставка переноса строки после input`a
     * @param bool|int $size ширина поля ввода в символах
     * @param boolean $disabled блокировать изменения
     * @param bool|int $maxlength максимальная вместимость в символах
     * @param string $class имя класса если нужен (по-умолчанию отключен)
     * @param string $icon имя иконки если нужна (по-умолчанию отключена)
     * @return boolean
     */
    function input($name, $title, $value = '', $type = 'text', $br = false, $size = false, $disabled = false, $maxlength = false, $class = false, $icon = false) {
        if (!in_array($type, array('text', 'input_text', 'password', 'hidden', 'textarea', 'submit', 'file'))) {
            return false;
        }

        $input = array();

        if ($type == 'file') {
            $this->set_is_files();
        }

        if ($type == 'text') {
            $type = 'input_text';
        } // так уж изначально было задумано. Избавляться будем постепенно

        $input['type'] = $type;
        $input['title'] = text::toOutput($title);
        $input['br'] = (bool) $br;

        $info = array();
        $info['name'] = text::toValue($name);
        $info['value'] = $value;
        $info['class'] = $class;
        $info['icon'] = $icon;

        $info['disabled'] = (bool) $disabled;

        if ($size)
            $info['size'] = (int) $size;
        if ($maxlength)
            $info['maxlength'] = (int) $maxlength;

        $input['info'] = $info;
        $this->_data['el'][] = $input;
        return true;
    }

    /**
     * Установка метода передачи формы на сервер (post, get)
     * @param string $method
     */
    function set_method($method) {
        if (in_array($method, array('get', 'post')))
            $this->_data['method'] = $method;
    }

    /**
     * Установка URL (атрибут action формы)
     * @param string|url $url
     */
    function set_url($url) {
        $this->_data['action'] = (string) $url;
    }

    /**
     * Будут передаваться файлы
     */
    function set_is_files() {
        $this->_data['method'] = 'post';
        $this->_data['files'] = true;
        $this->_data['limit_files'] = ini_get('max_file_uploads');

        $upload_max_filesize = misc::returnBytes(ini_get('upload_max_filesize'));
        $post_max_size = misc::returnBytes(ini_get('post_max_size'));
        $memory_limit = misc::returnBytes(ini_get('memory_limit'));

        if ($memory_limit > 0) {
            $limit_size = min($upload_max_filesize, $post_max_size, $memory_limit);
        } else { // локально может отсутствовать лимит по памяти
            $limit_size = min($upload_max_filesize, $post_max_size);
        }

        $this->_data['limit_size'] = $limit_size;
    }

}
