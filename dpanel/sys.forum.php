<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(5);
$doc->title = __('Параметры форума');

if (isset($_POST['save'])) {
    $dcms->forum_theme_captcha = (int) !empty($_POST['forum_theme_captcha']);
    $dcms->forum_message_captcha = (int) !empty($_POST['forum_message_captcha']);
    $dcms->forum_search_captcha = (int) !empty($_POST['forum_search_captcha']);
    $dcms->forum_search_reg = (int) !empty($_POST['forum_search_reg']);
    $dcms->forum_files_upload_size = (int) ($_POST['forum_files_upload_size'] * 1024);
    $dcms->forum_rating_down_balls = (int) $_POST['forum_rating_down_balls'];
    $dcms->forum_rating_coefficient = floatval($_POST['forum_rating_coefficient']);
    $dcms->save_settings($doc);
}

$form = new form('?' . passgen());
$form->checkbox('forum_theme_captcha', __('Создание тем через капчу') . ' *', $dcms->forum_theme_captcha);
$form->checkbox('forum_message_captcha', __('Сообщения через капчу') . ' *', $dcms->forum_message_captcha);
$form->text('forum_files_upload_size', __('Макс. размер прикрепляемого файла (KB)'), (int) ($dcms->forum_files_upload_size / 1024));
$form->text('forum_rating_coefficient', __('Соотношение рейтинга сообщений с рейтингом пользователя'), floatval($dcms->forum_rating_coefficient));
$form->text('forum_rating_down_balls', __('Цена отрицательного рейтинга в баллах'), intval($dcms->forum_rating_down_balls));
$form->checkbox('forum_search_captcha', __('Поиск через капчу'), $dcms->forum_search_captcha);
$form->checkbox('forum_search_reg', __('Поиск только для зарегистрированных'), $dcms->forum_search_reg);
$form->bbcode('* - ' . __('На администрацию данные ограничения не распространяются'));
$form->button(__('Сохранить'), 'save');
$form->display();

$doc->ret(__('Управление'), '/dpanel/');
