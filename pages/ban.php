<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Бан');

if (isset($_GET['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
}

if (!$user->is_ban) {
    $doc->err(__('Нет активных банов'));
} elseif ($user->is_ban_full) {
    $doc->err(__('Вам запрещена любая активность на сайте'));
} elseif ($user->is_ban) {
    $doc->err(__('Вам запрещено писать на сайте'));
}

$q = $db->prepare("SELECT * FROM `ban` WHERE `id_user` = ? ORDER BY `id` DESC");
$q->execute(Array($user->id));

$listing = new listing();
while ($c = $q->fetch()) {
    $adm = new user($c['id_adm']);

    $post = $listing->post();

    $post->title = $adm->nick();
    $post->time = misc::when($c['time_start']);
    $post->icon($adm->icon());

    $post->content = __('Нарушение: %s', text::toValue($c['code'])) . "\n";
    if ($c ['time_start'] && TIME < $c ['time_start']) {
        $post->content .= '[b]' . __('Начало действия') . ':[/b]' . misc::when($c ['time_start']) . "\n";
    }
    if ($c['time_end'] === NULL) {
        $post->content .= '[b]' . __('Пожизненная блокировка') . "[/b]\n";
    } elseif (TIME < $c['time_end']) {
        $post->content .= __('Осталось: %s', misc::when($c['time_end'])) . "\n";
    }
    if ($c['link']) {
        $post->content .= __('Ссылка на нарушение: %s', $c['link']) . "\n";
    }

    $post->content .= __('Комментарий: %s', $c['comment']) . "\n";

    $post->content = text::toOutput($post->content);

    $post->highlight = (TIME < $c['time_end'] && TIME >= $c['time_start']);
}

$listing->display(__('Нарушения отсутствуют'));
