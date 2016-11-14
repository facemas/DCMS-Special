<?php

class widget_feed_files implements widget_feed_module
{


    /**
     * @param int $limit
     * @return \widget_feed_post[]
     */
    function getLastPosts($limit)
    {
        $posts = array();

        $dir = new files(FILES . '/.downloads');
        $content = $dir->getNewFiles();
        $files = &$content['files'];
        /** @var $files files_file[] */
        $new_files = count($files);

        if ($new_files) {
            for ($i = 0; $i < $new_files && $i < dcms::getInstance()->widget_items_count; $i++) {

                $ank = new user($files[$i]->id_user);
                $post = new listing_post();
                $post->title = text::toValue($files[$i]->runame);
                $post->url = "/files" . $files[$i]->getPath() . ".htm";
                $post->image = $files[$i]->image();
                $post->icon($files[$i]->icon());


                $w_post = new widget_feed_post();
                $w_post->icon('downloads');
                $w_post->id = 'widget_files_'.$files[$i]->id;
                $w_post->title = __('Добавлен новый файл');
                $w_post->sort_time_field = $files[$i]->time_add;
                $w_post->time = misc::when($files[$i]->time_add);
                $w_post->content = $post->fetch();
                if ($ank->id)
                    $w_post->bottom = $ank->nick();
                $posts[] = $w_post;
            }

        }

        return $posts;
    }
}