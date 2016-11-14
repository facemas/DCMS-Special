<?php

/* Модифицировали мод
 * densnet
 * S1S13AF7
 */

# принудительное отключение редиректа на поддомены, соответствующие типу браузера
$subdomain_theme_redirect_disable = true;

include_once '../sys/inc/start.php';

$doc = new document();
$doc->title = __('Авторизация');

if (!function_exists('logaut')) {

    function logaut($id, $method, $status) {
        global $db, $dcms; /* будем получать IP, ID браузера, домен, делать запросы */
        $ua = (string) @$_SERVER['HTTP_USER_AGENT'];

        $q = $db->prepare("SELECT * FROM `log_of_user_aut` WHERE `id_user` = :id AND `iplong` = :ip_long AND `browser_ua` = :ua AND `domain` = :domain AND `method` = :method AND `status` = :status ORDER BY `time` DESC LIMIT 1");
        $q->execute(Array(':id' => $id, ':ip_long' => $dcms->ip_long, ':ua' => $ua, ':domain' => $dcms->subdomain_main, ':method' => $method, ':status' => $status));

        if (!$row = $q->fetch()) {
            $res = $db->prepare("INSERT INTO `log_of_user_aut` (`id_user`,`method`,`iplong`, `time`, `id_browser`,`browser`,`browser_ua`,`domain`,`status`) VALUES (:id,:method,:ip_long,:time,:br_id,:br_name,:ua,:domain,:status)");
            $res->execute(Array(':id' => $id, ':ip_long' => $dcms->ip_long, ':ua' => $ua, ':domain' => $dcms->subdomain_main, ':method' => $method, ':status' => $status, ':br_id' => $dcms->browser_id, ':br_name' => $dcms->browser_name, ':time' => TIME));
        } else {
            $res = $db->prepare("UPDATE `log_of_user_aut` SET `time` = :time, `id_browser` = :br_id, `count` = `count` + 1 WHERE `id_user` = :id AND `iplong` = :ip_long AND `browser_ua` = :ua AND `domain` = :domain AND `method` = :method AND `status` = :status LIMIT 1");
            $res->execute(Array(':id' => $id, ':ip_long' => $dcms->ip_long, ':ua' => $ua, ':domain' => $dcms->subdomain_main, ':method' => $method, ':status' => $status, ':br_id' => $dcms->browser_id, ':time' => TIME));
        }
    }

}

if (isset($_GET['redirected_from']) && in_array($_GET['redirected_from'], array('light', 'pda', 'mobile', 'full'))) {
    $subdomain_var = "subdomain_" . $_GET['redirected_from'];
    if (isset($_GET['return'])) {
        $return = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $dcms->$subdomain_var . '.' . $dcms->subdomain_main . '/login.php?login_from_cookie&return=' . urlencode($_GET['return']);
    } else {
        $return = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $dcms->$subdomain_var . '.' . $dcms->subdomain_main . '/login.php?login_from_cookie&return=' . urlencode('/');
    }
} else {
    if (isset($_GET['return']) && !preg_match('/exit/', $_GET['return'])) {
        $return = $_GET['return'];
    } else {
        $return = '/';
    }
}


if ($user->group) {
    if (isset($_GET['auth_key']) && cache::get($_GET['auth_key']) === 'request') {
        cache::set($_GET['auth_key'], array('session' => $_SESSION, 'cookie' => $_COOKIE), 60);
    }

    $doc->clean();
    header('Location: ' . $return, true, 302);
    exit;
}

$need_of_captcha = cache_aut_failture::get($dcms->ip_long);

if ($need_of_captcha && (empty($_POST['captcha']) || empty($_POST['captcha_session']) || !captcha::check($_POST['captcha'], $_POST['captcha_session']))) {
    $doc->err(__('Проверочное число введено неверно'));
} elseif (isset($_POST['login']) && isset($_POST['password'])) {
    if (!$_POST['login']) {
        $doc->err(__('Введите логин'));
    } elseif (!$_POST['password']) {
        $doc->err(__('Введите пароль'));
    } else {
        $login = (string) $_POST['login'];
        $password = (string) $_POST['password'];

        $q = $db->prepare("SELECT `id`, `password` FROM `users` WHERE `login` = ? LIMIT 1");
        $q->execute(Array($login));
        if (!$row = $q->fetch()) {
            $doc->err(__('Логин "%s" не зарегистрирован', $login));
        } elseif (crypt::hash($password, $dcms->salt) !== $row['password']) {
            $need_of_captcha = true;
            cache_aut_failture::set($dcms->ip_long, true, 600); // при ошибке заставляем пользователя проходить капчу
            $doc->err(__('Вы ошиблись при вводе пароля'));
            logaut($row['id'], 'post', 0);
        } else {
            $user_t = new user($row['id']);
            if (!$user_t->group) {
                $doc->err(__('Ошибка при получении профиля пользователя'));
            } elseif ($user_t->a_code) {
                $doc->err(__('Аккаунт не активирован'));
            } else {
                $user = $user_t;
                cache_aut_failture::set($dcms->ip_long, false, 1);
                logaut($user->id, 'post', 1);

                if ($user->recovery_password) {
                    // если пользователь авторизовался, то ключ для восстановления ему больше не нужен
                    $user->recovery_password = '';
                }
                $_SESSION[SESSION_ID_USER] = $user->id;
                $_SESSION[SESSION_PASSWORD_USER] = $password;

                if (isset($_POST['save_to_cookie']) && $_POST['save_to_cookie']) {
                    setcookie(COOKIE_ID_USER, $user->id, TIME + 60 * 60 * 24 * 365);
                    setcookie(COOKIE_USER_PASSWORD, crypt::encrypt($password, $dcms->salt_user), TIME + 60 * 60 * 24 * 365);
                }
            }
        }
    }
} elseif (!empty($_COOKIE[COOKIE_ID_USER]) && !empty($_COOKIE[COOKIE_USER_PASSWORD])) {
    $tmp_user = new user($_COOKIE[COOKIE_ID_USER]);

    if (crypt::hash(crypt::decrypt($_COOKIE[COOKIE_USER_PASSWORD], $dcms->salt_user), $dcms->salt) === $tmp_user->password) {
        // если пользователь авторизовался, то ключ для восстановления ему больше не нужен
        if ($user->recovery_password) {
            $user->recovery_password = '';
        }
        logaut($tmp_user->id, 'cookie', 1);

        $user = $tmp_user;

        $_SESSION[SESSION_ID_USER] = $user->id;
        $_SESSION[SESSION_PASSWORD_USER] = crypt::decrypt($_COOKIE[COOKIE_USER_PASSWORD], $dcms->salt_user);
    } else {
        $need_of_captcha = true;
        cache_aut_failture::set($dcms->ip_long, true, 600); // при ошибке заставляем пользователя проходить капчу
        setcookie(COOKIE_ID_USER);
        setcookie(COOKIE_USER_PASSWORD);
        logaut($tmp_user->id, 'cookie', 0);
    }
}

if ($user->group) {
    // авторизовались успешно
    // удаляем информацию как о госте
    $res = $db->prepare("DELETE FROM `guest_online` WHERE `ip_long` = ? AND `browser` = ?;");
    $res->execute(Array($dcms->ip_long, $dcms->browser_name));

    if (isset($_GET['auth_key']) && cache::get($_GET['auth_key']) === 'request') {
        cache::set($_GET['auth_key'], array('session' => $_SESSION, 'cookie' => $_COOKIE), 60);
    }

    $doc->clean();
    header('Location: ' . $return, true, 302);
    exit;
}

$form = new form('?' . passgen() . '&amp;return=' . text::toValue($return));
$form->block('<div class="ui left fa input">');
$form->input('login', __('Логин'));
$form->block('<i class="fa fa-user fa-fw"></i>');
$form->block('</div><br />');
$form->block('<div class="ui left fa input">');
$form->password('password', __('Пароль') . ' [' . '[url=/pass.php]' . __('забыли') . '[/url]]');
$form->block('<i class="fa fa-lock fa-fw"></i>');
$form->block('</div><br />');
$form->checkbox('save_to_cookie', __('Запомнить меня'));
$form->html('<br />');

if ($need_of_captcha) {
    $form->captcha();
}
$form->button(__('Войти'));
$form->display();

if ($dcms->vk_auth_enable && $dcms->vk_app_id && $dcms->vk_app_secret) {
    $vk = new vk($dcms->vk_app_id, $dcms->vk_app_secret);
    $form = new form($vk->getAuthorizationUri('http://' . $_SERVER['HTTP_HOST'] . '/vk.php', 'email'));
    $form->button(null, null, false, 'tiny ui vk button', 'fa fa-vk fa-lg');
    $form->display();
}