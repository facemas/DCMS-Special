<?php

include_once '../sys/inc/start.php';

$doc = new document(4);

$doc->title = __('Рассылка новости');
$doc->ret(__('К новостям'), './');

$id = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `news` WHERE `id` = ? LIMIT 1");
$q->execute(Array($id));

if (!$news = $q->fetch()) {
    $doc->access_denied(__('Новость не найдена или уже удалена'));
}

$ank = new user($news['id_user']);

if ($ank->group > $user->group) {
    $doc->access_denied(__('У Вас нет прав для рассылки данной новости'));
}

if ($news['sended']) {
    $doc->access_denied(__('Новость уже была разослана'));
}

if (isset($_POST['send'])) {

    $mail_unsubscribe = array();
    $q = $db->query("SELECT * FROM `mail_unsubscribe`");
    while ($mu = $q->fetch()) {
        $mail_unsubscribe[$mu['email']] = $mu['code'];
    }

    $mailes = array();

    $q = $db->query("SELECT `reg_mail`, `email` FROM `users` ORDER BY `id`");

    while ($um = $q->fetch()) {
        if ($um['reg_mail']) {
            // по умолчанию отправляем только на регистрационные email`ы
            $mailes[] = $um['reg_mail'];
        } elseif ($um['email'] && !empty($_POST['sendToAnkMail'])) {
            // если регистрационный email отсутствует и разрешено слать на анкетный ящик, то шлем на него
            $mailes[] = $um['email'];
        }
    }

    $mailes_to_send = array();
    for ($i = 0; $i < count($mailes); $i++) {
        if (array_key_exists($mailes[$i], $mail_unsubscribe)) {
            if (!$mail_unsubscribe[$mailes[$i]]) {
                continue;
            }
        }
        $mailes_to_send[] = $mailes[$i];
    }

    $mailes_to_send = array_unique($mailes_to_send);

    if ($mailes_to_send) {
        $to_unsubscribe_table = array();
        $contents = array();
        for ($i = 0; $i < count($mailes_to_send); $i++) {
            if (array_key_exists($mailes_to_send[$i], $mail_unsubscribe)) {
                $unsubscribe_code = $mail_unsubscribe[$mailes_to_send[$i]];
            } else {
                $unsubscribe_code = passgen(32);
                $to_unsubscribe_table[$mailes_to_send[$i]] = $unsubscribe_code;
            }

            $t = new design();
            $t->assign('title', 'Новости');
            $t->assign('site', $dcms->sitename);
            $t->assign('content', text::toOutput($news['text']));
            $t->assign('email', $mailes_to_send[$i]);
            $t->assign('unsubscribe', 'http://' . $_SERVER['HTTP_HOST'] . '/unsubscribe.php?code=' . $unsubscribe_code);
            $contents[] = $t->fetch('file:' . H . '/sys/templates/mail.news.tpl');
        }
        mail::send($mailes_to_send, $news['title'], $contents);
        $res = $db->prepare("UPDATE `news` SET `sended` = '1' WHERE `id` = ? LIMIT 1");
        $res->execute(Array($id));

        $res = $db->prepare("INSERT INTO `mail_unsubscribe` (`email`, `code`) VALUES (?,?)");
        foreach ($to_unsubscribe_table AS $email => $code) {
            $res->execute(Array($email, $code));
        }
        $doc->msg(__('Новость успешно отправлена'));
    } else {
        $doc->err(__('Нет получателей новости'));
    }
    //header('Refresh: 1; url=./');
    exit;
}

$form = new form(new url());
$form->checkbox('sendToAnkMail', __('Задействовать анкетный email') . '*', true);
$form->bbcode('* ' . __('По-умолчанию рассылка производится только по регистрационным e-mail'));
$form->button(__('Разослать новость по Email'), 'send');
$form->display();
