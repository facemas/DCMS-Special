<?php

include_once '../sys/inc/start.php';
$doc = new document(1); // инициализация документа для браузера
$doc->title = __('Общие настройки');

if (isset($_POST['save'])) {
    // количество пунктов на страницу
    if (!empty($_POST['items_per_page'])) {
        $ipp = (int) $_POST['items_per_page'];
        if ($ipp >= 5 && $ipp <= 99) {
            $user->items_per_page = $ipp;
        } else {
            $doc->err(__('Недопустимое количество пунктов на страницу'));
        }
    }
    // временной сдвиг
    if (isset($_POST['time_shift'])) {
        $ipp = (int) $_POST['time_shift'];
        if ($ipp >= - 12 && $ipp <= 12) {
            $user->time_shift = $ipp;
        } else {
            $doc->err(__('Недопустимое время'));
        }
    }

    $doc->msg(__('Параметры успешно приняты'));
}

$form = new design();
$form->assign('method', 'post');
$form->assign('action', '?' . passgen());
$elements = array();

$elements[] = array('type' => 'input_text', 'title' => __('Пунктов на страницу') . ' (' . $dcms->browser_type . ') [5-99]', 'br' => 1, 'info' => array('name' => 'items_per_page', 'value' => $user->items_per_page));

$options = array(); // Врменной сдвиг
for ($i = - 12; $i < 12; $i++) {
    $options[] = array($i, date('G:i', TIME + $i * 60 * 60), $user->time_shift == $i);
}
$elements[] = array('type' => 'select', 'title' => __('Мое время'), 'br' => 1, 'info' => array('name' => 'time_shift', 'options' => $options));

$elements[] = array('type' => 'submit', 'br' => 0, 'info' => array('name' => 'save', 'value' => __('Применить'))); // кнопка
$form->assign('el', $elements);
$form->display('input.form.tpl');

$doc->ret(__('Личное меню'), '/menu.user.php');
