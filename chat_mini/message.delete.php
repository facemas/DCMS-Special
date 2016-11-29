<?php

include_once '../sys/inc/start.php';
$doc = new document(2);
$doc->title = __('Удаление сообщения');

if (!isset($_GET ['id']) || !is_numeric($_GET ['id'])) {
    $doc->toReturn('./');
    $doc->err(__('Ошибка выбора сообщения'));
    exit();
}
$id_message = (int) $_GET ['id'];

$q = $db->prepare("SELECT * FROM `chat_mini` WHERE `id` = ? LIMIT 1");
$q->execute(Array($id_message));

if (!$message = $q->fetch()) {
    $doc->toReturn('./');
    $doc->err(__('Сообщение не найдено'));
    exit();
}


$res = $db->prepare("DELETE FROM `chat_mini` WHERE `id` = ? LIMIT 1");
$res->execute(Array($id_message));
$doc->msg(__('Сообщение успешно удалено'));

$ank = new user($message ['id_user']);

$dcms->log('Чат', "Удаление сообщения от [url=/profile.view.php?id={$ank->id}]{$ank->login}[/url] ([when]$message[time][/when]):\n" . $message ['message']);

$doc->toReturn('./');
if (isset($_GET ['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET ['return']));
} else {
    $doc->ret(__('Вернуться'), './');
}
?>