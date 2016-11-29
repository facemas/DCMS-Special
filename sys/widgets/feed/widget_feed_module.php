<?php

interface widget_feed_module
{
    /**
     * @param int $limit
     * @return \widget_feed_post[]
     */
    function getLastPosts($limit);
}