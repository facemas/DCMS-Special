<?php

/**
 * Class api_chat_mini
 */
class api_chat_mini implements api_controller {

    public static function getMessages($request_data) {
        // начало списка
        $offset = !isset($request_data['offset']) ? 0 : (int) @$request_data['offset'];
        // кол-во писем
        $count = !isset($request_data['count']) ? 30 : (int) @$request_data['count'];

        $messages = db::me()->query("SELECT * FROM `chat_mini` LIMIT " . "$offset, $count")->fetchAll();

        return array('messages' => $messages);
    }

    public static function addMessage($request_data) {
        
    }

}
