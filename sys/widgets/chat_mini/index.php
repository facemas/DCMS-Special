<?php

defined('SOCCMS') or die;

$db = DB::me();

$res = $db->prepare("SELECT COUNT(*) FROM `chat_mini` WHERE `time` > ?");
$res->execute(Array(NEW_TIME));
$new_posts = $res->fetchColumn();


$res = $db->query("SELECT COUNT(*) FROM `users_online` WHERE `request` LIKE '/chat_mini/%'");
$users = $res->fetchColumn();

$listing = new ui_components();
$listing->ui_comment = true; //подключаем css comments
$listing->ui_segment = true; //подключаем css segment
$listing->class = 'segments minimal small comments';

$post = $listing->post();

$userOnlineChat = null;
$newComments = null;

if ($new_posts) {
    $newComments = '<i class="fa fa-comments-o fa-fw"></i> +' . $new_posts;
}
if ($users) {
    $userOnlineChat = " <i class='fa fa-user fa-fw'></i> " . __('%s', $users);
}
$post->head = "<h4 class='ui secondary segment'><a href='/chat_mini/'><i class='fa fa-comments-o fa-fw'></i> " . __('Чат') . "</a> <span style='float: right'>$userOnlineChat $newComments</span></h4>";

$q = $db->query("SELECT * FROM `chat_mini` ORDER BY `id` DESC LIMIT 5");

if ($arr = $q->fetchAll()) {
    foreach ($arr AS $message) {

        $ank = new user($message['id_user']);
        $post = $listing->post();

        $post->class = 'segment comment';
        $post->comments = true;
        $post->id = 'chat_post_' . $message['id'];
        $post->url = '/chat_mini/actions.php?id=' . $message['id'];
        $post->avatar = $ank->getAvatar();
        $post->image_a_class = 'avatar';
        $post->time = misc::timek($message['time']);
        $post->login = $ank->nick();
        $post->content = text::toOutput($message['message']);

        if ($user->group && ($user->id != $ank->id)) {
            $post->action(false, "/chat_mini/index.php?message=$message[id]&amp;reply", __('Ответить'));
        }
        if ($user->group) {
            $post->action(false, "/chat_mini/index.php?message=$message[id]&amp;quote", __('Цитировать'));
        }
        if ($user->group >= 2) {
            $post->action(false, "/chat_mini/message.delete.php?id=$message[id]", __('Удалить'));
        }
    }
}


$listing->display(__('Нет результатов'));

