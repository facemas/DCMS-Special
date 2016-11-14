<?php

include_once '../sys/inc/start.php';
$doc = new document(5);
$doc->title = __('Рефералы');

if (isset($_GET['id_site'])) {
    $id = (string)$_GET['id_site'];

    $q = $db->prepare("SELECT * FROM `log_of_referers_sites` WHERE `id` = ? LIMIT 1");
    $q->execute(Array($id));

    if (!$site = $q->fetch()) {
        header('Refresh: 1; url=?');
        $doc->ret('Вернуться', '?');
        $doc->err(__('Данные о сайте отсутствуют'));
        exit;
    }

    $doc->title = __('Рефералы с сайта "%s"', $site['domain']);

    $res = $db->prepare("SELECT COUNT(DISTINCT `full_url`) FROM `log_of_referers` WHERE `id_site` = ?");
    $res->execute(Array($id));
    $listing = new listing();
    $pages = new pages;
    $pages->posts = $res->fetchColumn();
    $res = $db->prepare("SELECT `full_url`, COUNT(*) AS `count`, MAX(`time`) AS `time` FROM `log_of_referers` WHERE `id_site` = ? GROUP BY `full_url` ORDER BY `time` DESC LIMIT " . $pages->limit);
    $res->execute(Array($id));
    while ($ref = $res->fetch()) {
        $post = $listing->post();
        $post->title = misc::when($ref['time']);
        $post->content[] = $ref['full_url'];
        $post->counter = $ref['count'];
    }
    $listing->display(__('Рефералы отсутствуют'));

    $pages->display("?id_site=$id&amp;"); // вывод страниц
    $doc->ret(__('Все рефералы'), '?');
    $doc->ret(__('Управление'), '/dpanel/');
    exit;
}

if (!$dcms->log_of_referers)
    $doc->err(__('Служба записи рефералов не запущена'));

switch (@$_GET['order']) {
    case 'count':
        $filter = 'count';
        $order = "`count` DESC";
        break;
    case 'domain':
        $filter = 'domain';
        $order = "`domain` ASC";
        break;
    default:
        $filter = 'time';
        $order = '`time` DESC';
        break;
}

$res = $db->query("SELECT COUNT(*) FROM `log_of_referers_sites`");
$pages = new pages;
$pages->posts = $res->fetchColumn();

// меню сортировки
$ord = array();
$ord[] = array("?order=time&amp;page={$pages->this_page}", __('Последние'), $filter == 'time');
$ord[] = array("?order=count&amp;page={$pages->this_page}", __('Переходы'), $filter == 'count');
$ord[] = array("?order=domain&amp;page={$pages->this_page}", __('Адрес'), $filter == 'domain');
$or = new design();
$or->assign('order', $ord);
$or->display('design.order.tpl');

$listing = new listing();

$q = $db->query("SELECT * FROM `log_of_referers_sites` ORDER BY $order LIMIT " . $pages->limit);
while ($ref = $q->fetch()) {
    $post = $listing->post();
    $post->title = text::toOutput($ref['domain']);
    $post->url = '?id_site=' . $ref['id'];
    $post->time = misc::when($ref['time']);
    $post->counter = $ref['count'];
}

$listing->display(__('Рефералы отсутствуют'));

$pages->display("?order=$filter&amp;"); // вывод страниц

if (!$dcms->log_of_referers) {
    $doc->act(__('Управление службами'), 'sys.daemons.php');
}
$doc->ret(__('Управление'), './');