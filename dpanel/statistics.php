<?php

include '../sys/inc/start.php';
$doc = new document(5);
$doc->title = __('Статистика');

if (!$dcms->log_of_visits) {
    $doc->err(__('Служба ведения статистики отключена'));
}

$res = db::me()->query("SELECT * FROM `log_of_visits_for_days` ORDER BY `time_day` DESC LIMIT 30");

$chart_hosts = new line_chart(__("Посетители за последний месяц"));
$chart_hosts->series[] = $s_hosts_full = new line_chart_series(__('С компьютера'));
$chart_hosts->series[] = $s_hosts_mobile = new line_chart_series(__('Со смартфона'));
$chart_hosts->series[] = $s_hosts_lite = new line_chart_series(__('С телефона'));
$chart_hosts->series[] = $s_hosts_robot = new line_chart_series(__('Поисковые роботы'));

$chart_hits = new line_chart(__("Переходы за последний месяц"));
$chart_hits->series[] = $s_hits_full = new line_chart_series(__('С компьютера'));
$chart_hits->series[] = $s_hits_mobile = new line_chart_series(__('Со смартфона'));
$chart_hits->series[] = $s_hits_lite = new line_chart_series(__('С телефона'));
$chart_hits->series[] = $s_hits_robot = new line_chart_series(__('Поисковые роботы'));

$all = $res->fetchAll();
$all = array_reverse($all);

foreach ($all as $data) {
    $chart_hosts->categories[] = date('d', $data['time_day']);
    $chart_hits->categories[] = date('d', $data['time_day']);

    $s_hosts_full->data[] = (int) $data['hosts_full'];
    $s_hosts_mobile->data[] = (int) $data['hosts_mobile'];
    $s_hosts_lite->data[] = (int) $data['hosts_light'];
    $s_hosts_robot->data[] = (int) $data['hosts_robot'];

    $s_hits_full->data[] = (int) $data['hits_full'];
    $s_hits_mobile->data[] = (int) $data['hits_mobile'];
    $s_hits_lite->data[] = (int) $data['hits_light'];
    $s_hits_robot->data[] = (int) $data['hits_robot'];
}

$chart_hosts->display();
$chart_hits->display();
