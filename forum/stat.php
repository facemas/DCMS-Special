<?php

include_once '../sys/inc/start.php';
$doc = new document(5);
$doc->title = __('Форум - Статистика');

$s_time = 86400; // кол-во секунд в сутках

$cats = db::me()->query('SELECT * FROM `forum_categories`')->fetchAll();

$chart = new line_chart(__("Просмотры тем за последний месяц"));
foreach ($cats AS $cat) {
    $series = new line_chart_series($cat['name']);

    $i = 30;
    $cats_empty = true; // ось X для чарта еще не заполнена
    $fields = array();
    while ($i--) {
        $time_end = DAY_TIME - $s_time * ($i - 1);
        $time_start = $time_end - $s_time;
        if ($cats_empty) {
            $chart->categories[] = date('d', $time_start);
        }

        $field = 'count_' . $i;
        $fields[$field] = "(SELECT COUNT(*) FROM `forum_views` AS `fv` LEFT JOIN `forum_themes` AS `ft` ON `fv`.`id_theme` = `ft`.`id` WHERE `ft`.`id_category` = $cat[id] AND `fv`.`time` >= $time_start AND `fv`.`time` < $time_end) AS $field";
    }

    $q = db::me()->query('SELECT ' . implode(',', array_values($fields)));
    $result = $q->fetch();
    foreach ($fields AS $field => $sql) {
        $series->data[] = (int) $result[$field];
    }

    $cats_empty = false;
    $chart->series[] = $series;
}
$chart->display();

$chart = new line_chart(__("Новые сообщения в темах за последний месяц"));
foreach ($cats AS $cat) {
    $series = new line_chart_series($cat['name']);

    $i = 30;
    $cats_empty = true; // ось X для чарта еще не заполнена
    $fields = array();
    while ($i--) {
        $time_end = DAY_TIME - $s_time * ($i - 1);
        $time_start = $time_end - $s_time;
        if ($cats_empty) {
            $chart->categories[] = date('d', $time_start);
        }

        $field = 'count_' . $i;
        $fields[$field] = "(SELECT COUNT(*) FROM `forum_messages` AS `fm` WHERE `fm`.`id_category` = $cat[id] AND `fm`.`time` >= $time_start AND `fm`.`time` < $time_end) AS $field";
    }

    $q = db::me()->query('SELECT ' . implode(',', array_values($fields)));
    $result = $q->fetch();
    foreach ($fields AS $field => $sql) {
        $series->data[] = (int) $result[$field];
    }

    $cats_empty = false;
    $chart->series[] = $series;
}
$chart->display();

$doc->ret(__('Форум'), './');

