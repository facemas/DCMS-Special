<?php

include_once '../sys/inc/start.php';

$ank = (empty($_GET ['id'])) ? $user : new user((int) $_GET ['id']);
$doc = new document($user->id === $ank->id ? 1 : 5);

$doc->title = __('Журнал авторизаций') . ($user->id != $ank->id ? " $ank->login" : '');

$res = $db->prepare("SELECT COUNT(*) FROM `log_of_user_aut` WHERE `id_user` = ?");
$res->execute(Array($ank->id));

if (!$ank->group) {
    $doc->access_denied(__('Нет данных'));
}
if ($ank->group >= $user->group && $ank->id != $user->id) {
    $doc->access_denied(__('Доступ к данной странице запрещен'));
}
static $browsers = array();

$pages = new pages;
$pages->posts = $res->fetchColumn();

$q = $db->prepare("SELECT * FROM `log_of_user_aut` WHERE `id_user` = ? ORDER BY `time` DESC LIMIT " . $pages->limit . ";");
$q->execute(Array($ank->id));

$listing = new ui_components();
$listing->ui_segment = true; //подключаем css segments
$listing->class = 'segments';

while ($log = $q->fetch()) {
    $post = $listing->post();
    $post->class = 'ui segment';
    $post->ui_label = true;
    $post->list = true;
    $post->highlight = !$log['status'];
    $post->time = misc::when($log['time']);
    $post->counter = $log['count'];
    $post->title = $log['method'] . ': ' . __($log['status'] ? 'Удачно' : 'Не удачно');
    $post->content = "<b>IP: " . long2ip($log['iplong']) . "</b><br />";

    if ($log['browser']) {
        $post->content .= __('Браузер') . ": $log[browser]<br />";
    } else {
        if (!isset($browsers [$log['id_browser']]) && (int) $log['id_browser'] > 0) {
            $b = $db->prepare("SELECT * FROM `browsers` WHERE `id` = ? LIMIT 1;");
            $b->execute(Array($log['id_browser']));
            if ($t = $b->fetch()) {
                $browsers[$t['id']] = $t['name'];
            } else {
                $browsers[$log['id_browser']] = false;
            }
        }
        if ($browsers [$log['id_browser']]) {
            $post->content .= __('Браузер') . ": " . $browsers[$log['id_browser']] . "<br />";
        }
    }
    if ($log['browser_ua']) {
        $post->content .= "User-Agent: $log[browser_ua]";
    }
}
$listing->display(__('Журнал пуст'));

$pages->display('?');

$doc->opt(__('Личное меню'), '/menu.user.php');
