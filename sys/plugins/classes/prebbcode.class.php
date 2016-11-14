<?php

/**
 * Предварительная обработка BBCODE.
 * Используется для того, чтобы скрыть содержимое в теге HIDE, даже когда не нужна обработка BBCODE. Например, при цитировании
 */
class prebbcode extends bbcode {

    var $info_about_tags = array(
        'hide' => array(
            'handler' => 'hide_2bb',
            'is_close' => false,
            'lbr' => 0,
            'rbr' => 0,
            'ends' => array(),
            'permission_top_level' => true,
            'children' => array('hide')
        )
    );

    function __construct($code) {
        parent::__construct($code);
    }

    /**
     * Переопределение метода в bbcode
     * Возвращает принятую строку без изменений
     * @param string $text
     * @return string
     */
    function insert_smiles($text) {
        return $text;
    }

    function hide_2bb($elem) {
        global $user;
        if (!empty($elem['attrib']['balls']) && $elem['attrib']['balls'] > $user->balls) {
            return '[spoiler="' . __('Скрытый текст') . '"]' . __('Недостаточно баллов для отображения данного текста (Необходимо: %s)', $elem['attrib']['balls']) . '[/spoiler]';
        }
        if (!empty($elem['attrib']['group']) && $elem['attrib']['group'] > $user->group) {
            return '[spoiler="' . __('Скрытый текст') . '"]' . __('Недостаточно прав для отображения данного текста (Необходим статус: %s)', groups::name($elem['attrib']['group'])) . '[/spoiler]';
        }

        if (!$user->group) {
            return '[spoiler="' . __('Скрытый текст') . '"]' . __('Для просмотра данного текста необходимо авторизоваться') . '[/spoiler]';
        }

        return '[spoiler="' . __('Скрытый текст') . '"]' . $this->get_html($elem['val']) . '[/spoiler]';
    }

}
