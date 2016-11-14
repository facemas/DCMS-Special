<?php

include_once '../sys/inc/start.php';
$doc = new document(5);
$doc->title = __('Изменение порядка категорий');

if (isset($_GET['sortable'])) {
    $sort = explode(',', $_POST['sortable']);
    $q = $db->prepare("SELECT * FROM `forum_categories` WHERE `group_show` <= ? ORDER BY `position` ASC");
    $q->execute(Array($user->group));
    $res_up = $db->prepare("UPDATE `forum_categories` SET `position` = ? WHERE `id` = ?");
    while ($category = $q->fetch()) {
        if (($position = array_search('cid' . $category['id'], $sort)) !== false) {
            $res_up->execute(Array($position, $category['id']));
        }
    }

    $doc->clean();
    header('Content-type: application/json');
    echo json_encode(
            array(
                'result' => 1,
                'description' => __('Порядок категорий успешно сохранен')
    ));
    exit;
}

if (!empty($_GET['id']) && !empty($_GET['act'])) {
    $q = $db->prepare("SELECT * FROM `forum_categories` WHERE `group_show` <= ? ORDER BY `position` ASC");
    $q->execute(Array($user->group));
    $sort = array();
    while ($category = $q->fetch()) {
        $sort[$category['id']] = $category['id'];
    }

    switch ($_GET['act']) {
        case 'up':
            if (misc::array_key_move($sort, $_GET['id'], -1)) {
                $doc->msg(__('Категория успешно перемещена вверх'));
            } else {
                $doc->err(__('Категория уже находится вверху'));
            }
            break;

        case 'down':
            if (misc::array_key_move($sort, $_GET['id'], 1)) {
                $doc->msg(__('Категория успешно перемещена вниз'));
            } else {
                $doc->err(__('Категория уже находится внизу'));
            }
            break;
    }

    // сбрасываем ключи массива.
    // Остается порядковый массив с идентификаторами категорий
    $sort = array_values($sort);

    $q = $db->prepare("SELECT * FROM `forum_categories` WHERE `group_show` <= ? ORDER BY `position` ASC");
    $q->execute(Array($user->group));
    $res_up = $db->prepare("UPDATE `forum_categories` SET `position` = ? WHERE `id` = ?");

    while ($category = $q->fetch()) {
        if (($position = array_search($category['id'], $sort)) !== false) {
            $res_up->execute(Array($position, $category['id']));
        }
    }

    $doc->ret('Вернуться', '?' . passgen());
    header('Refresh: 1; url=?' . passgen());
    exit;
}

$listing = new listing();
$q = $db->prepare("SELECT * FROM `forum_categories` WHERE `group_show` <= ? ORDER BY `position` ASC");
$q->execute(Array($user->group));
while ($category = $q->fetch()) {
    $post = $listing->post();
    $post->id = 'cid' . $category['id'];
    $post->title = text::toValue($category['name']);
    $post->icon('folder');
    $post->post = text::for_opis($category['description']);

    $post->action('arrow-up', '?id=' . $category['id'] . '&amp;act=up');
    $post->action('arrow-down', '?id=' . $category['id'] . '&amp;act=down');
}
$listing->sortable = '?sortable';
$listing->display(__('Доступных Вам категорий нет'));

$doc->ret(__('Форум'), 'index.php');
