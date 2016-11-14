<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Мои темы');

$ank = (empty($_GET['id'])) ? $user : new user((int) $_GET['id']);
if (!$ank->group) {
    $doc->access_denied(__('Нет данных'));
}
$doc->title = ($ank->id == $user->id) ? __('Мои темы') : __('Темы пользователя "%s"', $ank->login);
$pages = new pages;
$res = $db->prepare("SELECT COUNT(DISTINCT(`msg`.`id_theme`)) FROM `forum_messages` AS `msg` LEFT JOIN `forum_themes` AS `th` ON `th`.`id` = `msg`.`id_theme` LEFT JOIN `forum_topics` AS `tp` ON `tp`.`id` = `th`.`id_topic` LEFT JOIN `forum_categories` AS `cat` ON `cat`.`id` = `th`.`id_category` WHERE `th`.`id_autor` = :aid AND `th`.`group_show` <= :ugr AND `tp`.`group_show` <= :ugr AND `cat`.`group_show` <= :ugr AND `msg`.`group_show` <= :ugr");
$res->execute(Array(':aid' => $ank->id, ':ugr' => $user->group));
$pages = new pages;
$pages->posts = $res->fetchColumn();

$q = $db->prepare("SELECT `th`.* , `tp`.`name` AS `topic_name`, `cat`.`name` AS `category_name`, `tp`.`group_write` AS `topic_group_write`, COUNT(`msg`.`id`) AS `count`,         (SELECT COUNT(`fv`.`id_user`) FROM `forum_views` AS `fv` WHERE `fv`.`id_theme` = `msg`.`id_theme`)  AS `views` FROM `forum_messages` AS `msg` LEFT JOIN `forum_themes` AS `th` ON `th`.`id` = `msg`.`id_theme` LEFT JOIN `forum_topics` AS `tp` ON `tp`.`id` = `th`.`id_topic` LEFT JOIN `forum_categories` AS `cat` ON `cat`.`id` = `th`.`id_category` WHERE `th`.`id_autor` = :aid AND `th`.`group_show` <= :ugr AND `tp`.`group_show` <= :ugr AND `cat`.`group_show` <= :ugr AND `msg`.`group_show` <= :ugr GROUP BY `msg`.`id_theme` ORDER BY MAX(`msg`.`time`) DESC LIMIT " . $pages->limit);
$q->execute(Array(':aid' => $ank->id, ':ugr' => $user->group));

$listing = new listing();
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $themes) {
        $is_open = (int) ($themes['group_write'] <= $themes['topic_group_write']);
        $post = $listing->post();
        $post->img = "/sys/images/icons/forum.theme.{$themes['top']}.$is_open.png";
        $post->time = misc::when($themes['time_last']);
        $post->title = text::toValue($themes['name']);
        $post->counter = $themes['count'];
        $post->url = 'theme.php?id=' . $themes['id'] . '&amp;page=end';
        $last_msg = new user($themes['id_last']);
        $post->content .= ($ank->id != $last_msg->id ? $ank->nick . '/' . $last_msg->nick : $ank->nick);
        $post->content .= text::toOutput("\n[url=category.php?id=$themes[id_category]]" . $themes['category_name'] . "[/url] > [url=topic.php?id=$themes[id_topic]]" . $themes['topic_name'] . "[/url]");
        $post->bottom = __('Просмотров: %s', $themes['views']);
    }
}

$listing->display(($ank->id == $user->id) ? __('Созданных Вами тем не найдено') : __('%s еще не создавал' . ($ank->sex ? '' : 'а') . ' тем на форуме', $ank->login));

$pages->display("?id=$ank->id&amp;"); // вывод страниц
$doc->ret(__('Форум'), './');
