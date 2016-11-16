<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Редактирование');
if (!isset($_GET ['id']) || !is_numeric($_GET ['id'])) {
    if (isset($_GET ['return'])) {
        header('Refresh: 1; url=' . $_GET ['return']);
    } else {
        header('Refresh: 1; url=./');
    }
    $doc->err(__('Запись не выбрана'));
    exit();
}
$id_blog = (int) $_GET['id'];
$q = $db->prepare("SELECT * FROM `blog` WHERE `id` = ?");
$q->execute(Array($id_blog));
if (!$blogs = $q->fetch()) {
    if (isset($_GET ['return'])) {
        header('Refresh: 1; url=' . $_GET ['return']);
    } else {
        header('Refresh: 1; url=./');
    }
    $doc->err(__('Записи не существует'));
    exit;
}
$autor = new user((int) $blogs['autor']);
if ($autor->id == $user->id || $user->group >= 2) {
    $doc->title .= ' - ' . $blogs ['name'];
    if (isset($_POST['message']) && isset($_POST['name'])) {
        $message = text::input_text($_POST ['message']);
        $name = text::for_name($_POST ['name']);
        if ($dcms->censure && $mat = is_valid::mat($message)) {
            $doc->err(__('Обнаружен мат: %s', $mat));
        } elseif ($dcms->censure && $mat = is_valid::mat($name)) {
            $doc->err(__('Обнаружен мат: %s', $mat));
        } elseif ($message && $name) {
            $q = $db->prepare("UPDATE `blog` SET `message` = ?, `name` = ? WHERE `id` = ?  LIMIT 1");
            $q->execute(Array($message, $name, $blogs['id']));
            $doc->msg(__('Запись успешно изменена'));
            header('Refresh: 1; url=blog.php?blog=' . $blogs ['id']);
            exit();
        } else {
            $doc->err(__('Текст или название записи пусты'));
        }
    }
    $form = new form('?id=' . $id_blog . '&amp;' . passgen());
    $form->text('name', __('Название записи'), $blogs['name']);
    $form->textarea('message', __('Редактирование текста'), $blogs['message']);
    $form->button(__('Применить'));
    $form->display();
    $doc->opt(__('Вложения'), 'files.blog.php?id=' . $blogs['id'] . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
} else {
    $doc->err(__('Доступ ограничен'));
}
$doc->ret(__('К записи'), 'blog.php?blog=' . $blogs['id'] . '');
$doc->ret(__('Блоги'), 'index.php');
?>