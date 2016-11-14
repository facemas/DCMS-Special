<?php

include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Авторизация');

if (!$dcms->vk_auth_enable) {
    $doc->err(__('Авторизация через vk.com не доступна'));
    exit;
}

if (!empty($_GET['error'])) {
    if (!empty($_GET['error_description'])) {
        $doc->err(text::toOutput($_GET['error_description']));
    } else {
        $doc->err(__('Не удалось авторизоваться'));
    }
    exit;
}

if (empty($_GET['code'])) {
    header("Location: /");
    exit;
}

if (!$dcms->vk_app_id || !$dcms->vk_app_secret) {
    header("Location: /");
    exit;
}

try {
    $vk = new vk($dcms->vk_app_id, $dcms->vk_app_secret);
    $vk->getAccessToken('http://' . $_SERVER['HTTP_HOST'] . '/vk.php', $_GET['code']);
    $vk_user = $vk->getCurrentUser();

    echo '<!--' . json_encode($vk_user) . '-->';

    if ($vk->getEmail()) {
        $q = $db->prepare("SELECT * FROM `users` WHERE `reg_mail` = :email ORDER BY `last_visit` DESC LIMIT 1");
        $q->execute(array(':email' => $vk->getEmail()));
        if ($q->rowCount()) {
            $user_data = $q->fetch();
            misc::logaut($user_data['id'], 'vk', 1, $vk_user['uid']);
            $_SESSION [SESSION_ID_USER] = $user_data['id'];
            $doc->msg(__("Авторизация прошла успешно"));

            header('Refresh: 1; url=/index.php');
            exit;
        }
    }

    $q = $db->prepare("SELECT * FROM `users` WHERE `vk_id` = :id_vk LIMIT 1");
    $q->execute(array(':id_vk' => $vk_user['uid']));
    if ($q->rowCount()) {
        $user_data = $q->fetch();
        misc::logaut($user_data['id'], 'vk', 1, $vk_user['uid']);
        $_SESSION [SESSION_ID_USER] = $user_data['id'];
        if (empty($user_data['reg_mail']) && $vk->getEmail()) {
            $q = $db->prepare("UPDATE `users` SET `reg_mail` = :email WHERE `vk_id` = :vk_id LIMIT 1");
            $q->execute(array(':vk_id' => $vk_user['uid'], ':email' => $vk->getEmail()));
        }
        $doc->msg(__("Авторизация прошла успешно"));

        header('Refresh: 1; url=/index.php');

        exit;
    } else if (!$dcms->vk_reg_enable) {
        misc::logaut(0, 'vk', 0, $vk_user['uid']);
        throw new Exception(__('Регистрация через vk.com запрещена'));
    }

    $res = $db->prepare("SELECT * FROM `users` WHERE login =?;");
    $res->execute(Array($vk_user['domain'])); // проверяем не занят-ли логин
    $login = (!$res->fetch() && is_valid::nick($vk_user['domain']) && $vk_user['domain'] != "id" . $vk_user['uid']) ? $vk_user['domain'] : '$vk.' . $vk_user['uid'];

    $res = $db->prepare("INSERT INTO `users` (`reg_date`, `login`, `password`, `sex`, `reg_mail`, `vk_id`, `vk_first_name`, `vk_last_name`, `realname`, `lastname`, `email`, `language`) VALUES (:reg_date, :login, :pass, :sex, :reg_mail, :vk_id, :vk_first_name, :vk_last_name, :vk_first_name, :vk_last_name, :reg_mail, :language)");
    $res->execute(Array(
        ':reg_date' => TIME,
        ':login' => $login,
        ':pass' => $vk->getAccessToken(),
        ':sex' => ($vk_user['sex'] == 0 || $vk_user['sex'] == 2) ? 1 : 0,
        ':reg_mail' => $vk->getEmail() ? $vk->getEmail() : '',
        ':vk_id' => $vk_user['uid'],
        ':vk_first_name' => $vk_user['first_name'],
        ':vk_last_name' => $vk_user['last_name'],
        ':language' => $user_language_pack->code
    ));

    $id = $db->lastInsertId();
    $_SESSION [SESSION_ID_USER] = $id;
    $doc->msg(__("Регистрация прошла успешно"));

    header('Refresh: 1; url=/index.php');
} catch (Exception $e) {
    $doc->err(__('Не удалось авторизоваться: %s', $e->getMessage()));
    header('Refresh: 1; url=/login.php?');
    exit;
}
