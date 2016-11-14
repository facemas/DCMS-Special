<?php

class widget_feed_forum implements widget_feed_module
{

    /**
     * @param int $limit
     * @return \widget_feed_post[]
     */
    function getLastPosts($limit)
    {
        $posts = array();

        $all_messages = $this->_getLastPosts($limit);
        $users_for_preload = array();
        foreach ($all_messages AS $message) {
            $users_for_preload[] = $message['id_user'];
        }
        new user($users_for_preload);

        foreach ($all_messages AS $message) {
            $post = new listing_post();
            $post->id = 'message' . $message['id'];
            $ank = new user((int)$message['id_user']);
            $post->title = $ank->nick();
            $post->icon($ank->icon());
//            $post->time = misc::when($message['time']);
//            $post->url = 'message.php?id_message=' . $message['id'];
            $post->content = text::for_opis($message['message']);

            $w_post = new widget_feed_post();
            $w_post->icon('forum');
            $w_post->title = __('Сообщение в форуме', $message['theme_name']);
            $w_post->content = $post->fetch();
            $w_post->url = '/forum/message.php?id_message=' . $message['id'];
            $w_post->id = 'widget_' . $post->id;
            $w_post->time = misc::when($message['time']);
            $w_post->sort_time_field = $message['time'];
            $w_post->bottom = $message['category_name'] . ' &gt; ' . $message['topic_name'] . ' &gt; ' . $message['theme_name'];
            $posts[] = $w_post;
        }

        return $posts;
    }

    protected function _getLastPosts($limit)
    {
        $limit = (int)$limit;
        $q = db::me()->prepare("SELECT `fm`.* ,
        `th`.`name` AS `theme_name`,
        `tp`.`name` AS `topic_name`,
        `cat`.`name` AS `category_name`,
            GREATEST(`th`.`group_show`, `tp`.`group_show`, `cat`.`group_show`, `fm`.`group_show`) AS `greatest_group_show`
            FROM `forum_messages` AS `fm`
            JOIN `forum_themes` AS `th` ON `th`.`id` = `fm`.`id_theme`
            JOIN `forum_topics` AS `tp` ON `tp`.`id` = `th`.`id_topic`
            JOIN `forum_categories` AS `cat` ON `cat`.`id` = `th`.`id_category`
            WHERE GREATEST(`th`.`group_show`, `tp`.`group_show`, `cat`.`group_show`, `fm`.`group_show`) <= :group_show
            ORDER BY `fm`.`id` DESC LIMIT " . $limit);
        $q->execute(array(':group_show' => current_user::getInstance()->group));

        return $q->fetchAll();
    }

}