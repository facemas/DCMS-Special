<?php

if (!defined('SOCCMS')) {
    require_once '../../inc/start.php';
    $doc = new document_json();
}

/**
 * @param widget_feed_post $post1
 * @param widget_feed_post $post2
 * @return int
 */
function widget_feed_sort_callback($post1, $post2)
{
    return $post1->sort_time_field == $post2->sort_time_field ? 0 : $post1->sort_time_field > $post2->sort_time_field ? -1 : 1;
}

function widget_feed_start_with($haystack, $needle)
{
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

require_once dirname(__FILE__) . '/widget_feed_module.php';
require_once dirname(__FILE__) . '/widget_feed_post.php';

require_once dirname(__FILE__) . '/modules/forum.php';
require_once dirname(__FILE__) . '/modules/chat.php';
require_once dirname(__FILE__) . '/modules/users.php';
require_once dirname(__FILE__) . '/modules/files.php';
require_once dirname(__FILE__) . '/modules/obmen.php';

$user = current_user::getInstance();


$listing = new listing();
$listing->setAjaxUrl(new url('/sys/widgets/feed/feed.php', array('feed_update' => 'update')));

$posts = array();

$classes = get_declared_classes();
/**
 * @var $modules_obj \widget_feed_module[]
 */
$modules_obj = array();
foreach ($classes AS $class_name) {
    if (!widget_feed_start_with($class_name, 'widget_feed_'))
        continue;

    $obj = new $class_name();
    if ($obj instanceof widget_feed_module) {
        $modules_obj[] = $obj;
    } else {
        unset($obj);
    }
}

$all_posts = array();
foreach ($modules_obj AS $module_obj) {
    $all_posts = array_merge($all_posts, $module_obj->getLastPosts(dcms::getInstance()->widget_items_count));
}

usort($all_posts, 'widget_feed_sort_callback');

$after_id = null;

/**
 * @var widget_feed_post $post
 */
foreach ($all_posts AS $post) {
    $listing->add($post);
    if (isset($doc) && $doc instanceof document_json)
        $doc->add_post($post, $after_id);
    $after_id = $post->id;
}

if (!isset($doc))
    $listing->display();
