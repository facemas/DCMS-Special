<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Отписаться от рассылки');
$unsubscribe = false;

if (!empty($_GET['code'])) {
    $code = $_GET['code'];
} else if (!empty($_POST['code'])) {
    $code = $_POST['code'];
    $unsubscribe = true;
} else {
    $doc->access_denied(__('Не передан код'));
}

$res = $db->prepare("SELECT * FROM `mail_unsubscribe` WHERE `code` = ? LIMIT 1");
$res->execute(Array($code));

if (!$uns = $res->fetch()) {
    $doc->access_denied(__('Данный код недействителен'));
}

if ($unsubscribe) {
    $res = $db->prepare("UPDATE `mail_unsubscribe` SET `code` = '' WHERE `code` = ? LIMIT 1");
    $res->execute(Array($code));
    $doc->msg(__("E-mail %s успешно отписан от рассылки", $uns['email']));
    exit;
}

$form = new form('?' . passgen());
$form->hidden('code', $code);
$form->text('email', 'E-mail', $uns['email'], true, false, true);
$form->button(__('Отписаться'));
$form->display();
