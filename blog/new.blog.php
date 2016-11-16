<?php

include_once '../sys/inc/start.php';

$doc = new document(1);
$doc->title = __('Новая запись');

if (!isset($_GET ['id_cat']) || !is_numeric($_GET ['id_cat'])) {
    $doc->toReturn();
    $doc->err(__('Ошибка выбора раздела'));
    exit();
}

$id_cat = (int) $_GET['id_cat'];
$q = $db->prepare("SELECT * FROM `blog_cat` WHERE `id` = ?");
$q->execute(Array($id_cat));
if (!$cat = $q->fetch()) {
    $doc->toReturn();
    $doc->err(__('В выбранной категории запрещено создавать разделы'));
    exit;
}

if (isset($_POST['message']) && isset($_POST['name'])) {
    $message = text::input_text($_POST ['message']);
    $name = text::for_name($_POST['name']);
    $users_in_message = text::nickSearch($message);
    
    if ($dcms->censure && $mat = is_valid::mat($message)) {
        $doc->err(__('Обнаружен мат: %s', $mat));
    } elseif ($dcms->censure && $mat = is_valid::mat($name)) {
        $doc->err(__('Обнаружен мат: %s', $mat));
    } elseif ($message && $name) {

        $res = $db->prepare("INSERT INTO `blog` (`name`,`autor`,`id_cat`,`time_create`,`message`) VALUES (?, ?, ?, ?, ?)");
        $res->execute(Array($name, $user->id, $cat['id'], TIME, $message));
        $blog = $db->lastInsertId();
        $doc->msg(__('Запись успешно добавлена'));
        header('Refresh: 1; url=blog.php?blog=' . $blog);
        exit();
    } else {
        $doc->err(__('Текст или название записи пусты'));
    }
}

$form = new form("?id_cat=$cat[id]&amp;" . passgen() . (isset($_GET ['return']) ? '&amp;return=' . urlencode($_GET ['return']) : null));
$form->text('name', __('Заголовок записи'));
$form->textarea('message', __('Содержимое'));

$form->button(__('Опубликовать'));
$form->display();

$doc->act(__('Категории'), 'index.php');
?>