<?php

include_once '../sys/inc/start.php';
$doc = new document(5);
$doc->title = __('Форум');
$doc->ret(__('К категориям'), './');
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора категории'));
    exit;
}
$id_cat = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `forum_categories` WHERE `id` = ? AND `group_show` <= ?");
$q->execute(Array($id_cat, $user->group));
if (!$topic_this = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Категория не доступна'));
    exit;
}

$doc->title = __('Форум - Статистика') . ' - ' . $topic_this['name'];

$s_time = 86400; // кол-во секунд в сутках

$p = db::me()->prepare('SELECT * FROM `forum_topics` WHERE `id_category` = :id_cat');
$p->execute(array(':id_cat' => $id_cat));
$topics = $p->fetchAll();

$chart = new line_chart(__("Просмотры тем за последний месяц"));
foreach ($topics AS $topic) {
    $series = new line_chart_series($topic['name']);

    $i = 30;
    $topics_empty = true; // ось X для чарта еще не заполнена
    $fields = array();
    while ($i--) {
        $time_end = DAY_TIME - $s_time * ($i - 1);
        $time_start = $time_end - $s_time;
        if ($topics_empty) {
            $chart->categories[] = date('d', $time_start);
        }

        $field = 'count_' . $i;
        $fields[$field] = "(SELECT COUNT(*) FROM `forum_views` AS `fv` LEFT JOIN `forum_themes` AS `ft` ON `fv`.`id_theme` = `ft`.`id` WHERE `ft`.`id_topic` = $topic[id] AND `fv`.`time` >= $time_start AND `fv`.`time` < $time_end) AS $field";
    }

    $q = db::me()->query('SELECT ' . implode(',', array_values($fields)));
    $result = $q->fetch();
    foreach ($fields AS $field => $sql) {
        $series->data[] = (int) $result[$field];
    }

    $topics_empty = false;
    $chart->series[] = $series;
}
$chart->display();

$chart = new line_chart(__("Новые сообщения в темах за последний месяц"));
foreach ($topics AS $topic) {
    $series = new line_chart_series($topic['name']);

    $i = 30;
    $topics_empty = true; // ось X для чарта еще не заполнена
    $fields = array();
    while ($i--) {
        $time_end = DAY_TIME - $s_time * ($i - 1);
        $time_start = $time_end - $s_time;
        if ($topics_empty)
            $chart->categories[] = date('d', $time_start);

        $field = 'count_' . $i;
        $fields[$field] = "(SELECT COUNT(*) FROM `forum_messages` AS `fm` WHERE `fm`.`id_topic` = $topic[id] AND `fm`.`time` >= $time_start AND `fm`.`time` < $time_end) AS $field";
    }

    $q = db::me()->query('SELECT ' . implode(',', array_values($fields)));
    $result = $q->fetch();
    
    foreach ($fields AS $field => $sql) {
        $series->data[] = (int) $result[$field];
    }

    $topics_empty = false;
    $chart->series[] = $series;
}
$chart->display();

if (isset($_GET['return'])) {
    $doc->ret(__('В категорию'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('В категорию'), 'category.php?id=' . $category['id']);
}

$doc->ret(__('Форум'), './');

