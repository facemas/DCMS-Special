<?php

include_once '../../sys/inc/start.php';

dpanel::check_access();

$doc = new document(4);
$doc->title = __('Картинка');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора подарка'));
    exit;
}

$id_present = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `present_items` WHERE `id` = ?");
$q->execute(Array($id_present));

if (!$item = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Подарок не доступен'));
    exit;
}

$doc->title .= ' - ' . $item['name'];

if (!empty($_FILES ['file'])) {
    if ($_FILES ['file'] ['error']) {
        $doc->err(__('Ошибка при загрузке'));
    } elseif (!$_FILES ['file'] ['size']) {
        $doc->err(__('Содержимое файла пусто'));
    } else {
        if (@move_uploaded_file($_FILES ['file'] ['tmp_name'], H . '/sys/images/presents/' . $id_present . '.png')) {
            $doc->msg(__('Изображение успешно загружено'));

            if (isset($_GET['return'])) {
                header('Refresh: 1; url=' . $_GET['return']);
                $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
            } else {
                header('Refresh: 1; url=category.php?id=' . $item['id_category'] . '&' . passgen());
                $doc->ret(__('В категорию'), 'category.php?id=' . $item['id_category']);
            }
            exit;
        } else {
            $doc->err(__('Не удалось сохранить изображение'));
        }
    }
}
$form = new form('?id=' . $id_present . '&' . passgen() . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
$form->file('file', __('Файл'));
$form->button(__('Создать'));
$form->display();

if (isset($_GET['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('В категорию'), 'category.php?id=' . $item['id_category']);
}