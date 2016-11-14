<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Ошибка');

// возврат туда, откуда пришли
$return = false;

switch (@$_GET['err']) {
    case 400:$doc->err(__('Обнаруженная ошибка в запросе'));
        $return = true;
        break;
    case 401:$doc->err(__('Нет прав для выдачи документа'));
        $return = true;
        break;
    case 402:$doc->err(__('Не реализованный код запроса'));
        $return = true;
        break;
    case 403:$doc->err(__('Доступ запрещен'));
        $return = true;
        break;
    case 404:$doc->err(__('Нет такой страницы'));
        $return = true;
        break;
    case 500:$doc->err(__('Внутренняя ошибка сервера'));
        break;
    case 502:$doc->err(__('Сервер получил недопустимые ответы другого сервера'));
        break;
    default: $doc->err(__('Неизвестная ошибка'));
        break;
}

if ($return && isset($_SERVER['HTTP_REFERER']) && preg_match('/' . preg_quote($_SERVER['HTTP_HOST']) . '/', $_SERVER['HTTP_REFERER'])) {
    header('Refresh: 1; url=' . $_SERVER['HTTP_REFERER']);
}