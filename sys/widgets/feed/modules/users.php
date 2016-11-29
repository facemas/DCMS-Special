<?php

class widget_feed_users implements widget_feed_module
{

    /**
     * @param int $limit
     * @return \widget_feed_post[]
     */
    function getLastPosts($limit)
    {
        $posts = array();
        $limit = (int)$limit;

        $res = db::me()->prepare("SELECT COUNT(*) FROM `users` WHERE `a_code` = '' AND `reg_date` > ?");
        $res->execute(Array(NEW_TIME));
        $users_count = $res->fetchColumn();


        if ($users_count) {
            $q = db::me()->prepare("SELECT * FROM `users` WHERE `a_code` = '' AND `reg_date` > ? ORDER BY `id` DESC LIMIT " . $limit);
            $q->execute(Array(NEW_TIME));

            if ($arr = $q->fetchAll()) {

                $last_reg_time = 0;
                $preload = array();
                foreach ($arr AS $ank) {
                    $preload[] = $ank['id'];
                    $last_reg_time = $last_reg_time < $ank['reg_date'] ? $ank['reg_date'] : $last_reg_time;
                }
                new user($preload);


                $post = new widget_feed_post();
                $post->id = 'widget_users_' . $last_reg_time;
                $post->sort_time_field = $last_reg_time;
                $post->title = __("Новые регистрации");
                $post->icon('users');
                $post->time = misc::when($last_reg_time);



                $post->content = text::toOutput('[user]' . join('[/user], [user]', $preload) . '[/user]');

                $posts[] = $post;
            }
        }

        return $posts;
    }
}