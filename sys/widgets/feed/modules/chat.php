<?php

class widget_feed_chat implements widget_feed_module
{

    /**
     * @param int $limit
     * @return \widget_feed_post[]
     */
    function getLastPosts($limit)
    {
        $limit = (int)$limit;
        $limit = $limit < 0 ? 1 : $limit > 100 ? 100 : $limit;

        $posts = array();
        $q = db::me()->query("SELECT * FROM `chat_mini` ORDER BY `id` DESC LIMIT " . $limit);
        if ($arr = $q->fetchAll()) {
            foreach ($arr AS $message) {
                $ank = new user($message['id_user']);
                $post = new listing_post();
                $post->id = 'chat_post_' . $message['id'];
                $post->title = $ank->nick();
                $post->post = text::toOutput($message['message']);
                $post->icon($ank->icon());

                $w_post = new widget_feed_post();
                $w_post->icon('chat_mini');
                $w_post->title = __('Сообщение в чате');
                $w_post->content = $post->fetch();
                $w_post->url = '/chat_mini/#' . $message['id'];
                $w_post->id = 'widget_' . $post->id;
                $w_post->time = misc::when($message['time']);
                $w_post->sort_time_field = $message['time'];
                $posts[] = $w_post;
            }
        }
        return $posts;
    }

}