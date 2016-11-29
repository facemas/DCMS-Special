<?php

$subdomain_theme_redirect_disable = true; // принудительное отключение редиректа на поддомены, соответствующие типу браузера
include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Регистрация');

if ($user->group) {
    $doc->access_denied(__('Вы уже зарегистрированы'));
}

if (!$dcms->reg_open) {
    $doc->access_denied(__('Регистрация временно закрыта'));
}

$error = false;
// пригласительный
$inv = &$_SESSION['reg']['invite'];
if (!$inv && isset($_GET['invite']) && $_GET['invite']) {
    $q = $db->prepare("SELECT * FROM `invations` WHERE `code` = ? AND `id_invite` IS NULL AND `email` IS NOT NULL LIMIT 1");
    $q->execute(Array($_GET['invite']));
    if ($inv = $q->fetch()) {

        // $doc->msg('Пригласительный учтен');

        $res = $db->prepare("UPDATE `invations` SET `code` = null WHERE `id` = ? LIMIT 1");
        $res->execute(Array($inv['id']));
    } else {
        $doc->err(__('Пригласительный недействителен'));
    }
}

if (!isset($inv)) {
    $inv = false;
}

if (!$inv && $dcms->reg_with_invite) {
    $doc->access_denied(__('Регистрация возможна только по приглашению'));
}

$step = &$_SESSION['reg']['step'];
if (!isset($step)) {
    $step = $dcms->reg_with_rules ? 0 : 1;
}
$login = &$_SESSION['reg']['login'];
$step_name = isset($_GET['step']) ? $_GET['step'] : null;
// принимаем правила
if ($step == 0 && $step_name === 'rules') {
    if ($_POST['ok']) {
        $step = 1;
        $doc->msg(__('Очень хорошо, надеемся на их соблюдение'));
    } elseif ($_POST['no']) {
        $doc->err(__('Для продолжения регистрации необходимо принять правила сайта'));
    }
}
// выбираем ник
if ($step == 1 && $step_name === 'nick' && isset($_POST['login'])) {
    if (is_valid::nick($_POST['login'])) {
        $login = $_POST['login'];
        $res = $db->prepare("SELECT * FROM `users` WHERE login =?;");
        $res->execute(Array($login));
        if (!$res->fetch()) {
            if ($_POST['login'] != htmlspecialchars($_POST['login'])) {
                $doc->err(__('В логине содержатся запрещенные символы'));
            } else {

                $step = 2;
                $_SESSION['reg']['login'] = $_POST['login'];
                $doc->msg(__('Логин может быть успешно зарегистрирован'));
            }
        } else {
            $doc->err(__('Логин занят другим пользователем'));
        }
    } else {
        $doc->err(__('Недопустимый логин'));
    }
}
// выбираем логин
if ($step == 2 && $step_name === 'final' && isset($_POST['sex'])) {
    $sex = $_POST['sex'] ? 1 : 0;

    $ank_dr = $_POST['ank_d_r'];
    $ank_mr = $_POST['ank_m_r'];
    $ank_gr = $_POST['ank_g_r'];

    if ($dcms->reg_with_mail && !$inv) {
        if (empty($_POST['mail'])) {
            $doc->err(__('Необходимо указать E-mail'));
            $error = true;
        }
        if (!is_valid::mail($_POST['mail'])) {
            $doc->err(__('Указан не корректный E-mail'));
            $error = true;
        }

        $res = $db->prepare("SELECT * FROM users WHERE `reg_mail`=?");
        $res->execute(Array($_POST['mail']));
        if ($res->fetch()) {
            $doc->err(__('Пользователь с таким e-mail уже зарегистрирован'));
            $error = true;
        }
        if (empty($_POST['password'])) {
            $doc->err(__('Необходимо указать пароль'));
            $error = true;
        }
        if (empty($_POST['password_retry'])) {
            $doc->err(__('Необходимо подтвердить пароль'));
            $error = true;
        }
        if ($_POST['password_retry'] != $_POST['password']) {
            $doc->err(__('Введенные пароли не совпадают'));
            $error = true;
        }
        if (!is_valid::password($_POST['password'])) {
            $doc->err(__('Не корректный пароль'));
            $error = true;
        }

        if (empty($_POST['captcha']) || empty($_POST['captcha_session']) || !captcha::check($_POST['captcha'], $_POST['captcha_session'])) {
            $doc->err(__('Проверочное число введено неверно'));
            $error = true;
        }

        //Если нет ошибок
        if (!$error) {

            $a_code = md5(passgen());

            $res = $db->prepare("INSERT INTO `users` (`reg_date`, `login`, `password`, `sex`, `a_code`, `reg_mail`, `ank_d_r`, `ank_m_r`, `ank_g_r`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $res->execute(Array(TIME, $_SESSION['reg']['login'], crypt::hash($_POST['password'], $dcms->salt), $sex, $a_code, $_POST['mail'], $ank_dr, $ank_mr, $ank_gr));
            $id_user = $db->lastInsertId();

            if ($id_user && is_numeric($id_user)) {

                if ($susp = is_valid::suspicion($inv['email'] . ' ' . $_SESSION['reg']['login'])) {
                    // подозрительный e-mail или логин
                    $res = $db->prepare("INSERT INTO `users_suspicion` (`id_user`, `text`) VALUES (?, ?)");
                    $res->execute(Array($id_user, $susp));
                    $dcms->distribution("Пользователь [user]{$id_user}[/user] сочтен подозрительным, так как в нике или адресе email была обнаружена несвязная комбинация символов: {$susp}\n[url=/dpanel/users.suspicious.php]Список подозрительных пользователей[/url]", 4);
                }


                $t = new design();
                $t->assign('title', __('Успешная регистрация'));
                $t->assign('login', $_SESSION['reg']['login']);
                $t->assign('password', $_POST['password']);
                $t->assign('site', $dcms->sitename);
                $t->assign('url', 'http://' . $_SERVER['HTTP_HOST'] . '/activation.php?id=' . $id_user . '&amp;code=' . $a_code . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
                if (mail::send($_POST['mail'], 'Регистрация', $t->fetch('file:' . H . '/sys/templates/mail.activation.tpl'))) {
                    $step = 3;
                    //$doc->msg(__('На Ваш E-mail отправлено письмо с ссылкой для активации аккаунта'));
                } else
                    $doc->err(__('Ошибка при отправке email, попробуйте позже'));
            } else {
                $doc->err(__('Ошибка при регистрации. Попробуйте позже'));
                $step = 1;
            }
        }
    } elseif ($inv) {
        if (empty($_POST['password'])) {
            $doc->err(__('Необходимо указать пароль'));
            $error = true;
        }

        if (!isset($_POST['password_retry'])) {
            $doc->err(__('Необходимо подтвердить пароль'));
            $error = true;
        }

        if ($_POST['password_retry'] != $_POST['password']) {
            $doc->err(__('Введенные пароли не совпадают'));
            $error = true;
        }
        if (!is_valid::password($_POST['password'])) {
            $doc->err(__('Не корректный пароль'));
            $error = true;
        }

        if (empty($_POST['captcha']) || empty($_POST['captcha_session']) || !captcha::check($_POST['captcha'], $_POST['captcha_session'])) {
            $doc->err(__('Проверочное число введено неверно'));
            $error = true;
        }

        $res = $db->prepare("SELECT * FROM `users` WHERE `reg_mail` = ?");
        $res->execute(Array($inv['email']));
        if ($res->fetch()) {
            $doc->err(__('Пользователь с таким e-mail уже зарегистрирован'));
            $error = true;
        }

        //Если нет ошибок
        if (!$error) {
            $res = $db->prepare("INSERT INTO `users` (`reg_date`, `login`, `password`, `sex`, `reg_mail`, `ank_d_r`, `ank_m_r`, `ank_g_r`) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
            $res->execute(Array(TIME, $_SESSION['reg']['login'], crypt::hash($_POST['password'], $dcms->salt), $sex, $inv['email'], $ank_dr, $ank_mr, $ank_gr));
            $id_user = $db->lastInsertId();

            if ($id_user && is_numeric($id_user)) {

                if ($susp = is_valid::suspicion($inv['email'] . ' ' . $_SESSION['reg']['login'])) {
                    // подозрительный e-mail или логин
                    $res = $db->prepare("INSERT INTO `users_suspicion` (`id_user`, `text`) VALUES (?, ?)");
                    $res->execute(Array($id_user, $susp));
                    $dcms->distribution("Пользователь [user]{$id_user}[/user] сочтен подозрительным, так как в нике или адресе email была обнаружена несвязная комбинация символов: {$susp}\n[url=/dpanel/users.suspicious.php]Список подозрительных пользователей[/url]", 4);
                }



                $res = $db->prepare("UPDATE `invations` SET `id_invite` = ?, `time_reg` = ? WHERE `id` = ? LIMIT 1");
                $res->execute(Array($id_user, TIME, $inv['id']));
                $res = $db->prepare("UPDATE `users` SET `balls` = `balls` * '1.1' WHERE `id` = ? LIMIT 1");
                $res->execute(Array($inv['id_user']));
                $step = 3;
            }
        }
    } else {
        if (empty($_POST['password'])) {
            $doc->err(__('Необходимо указать пароль'));
            $error = true;
        }

        if (!isset($_POST['password_retry'])) {
            $doc->err(__('Необходимо подтвердить пароль'));
            $error = true;
        }

        if ($_POST['password_retry'] != $_POST['password']) {
            $doc->err(__('Введенные пароли не совпадают'));
            $error = true;
        }
        if (!is_valid::password($_POST['password'])) {
            $doc->err(__('Не корректный пароль'));
            $error = true;
        }

        if (empty($_POST['captcha']) || empty($_POST['captcha_session']) || !captcha::check($_POST['captcha'], $_POST['captcha_session'])) {
            $doc->err(__('Проверочное число введено неверно'));
            $error = true;
        }

        //Если нет ошибок
        if (!$error) {
            $res = $db->prepare("INSERT INTO `users` (`reg_date`, `login`, `password`, `sex`, `ank_d_r`, `ank_m_r`, `ank_g_r`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $res->execute(Array(TIME, $_SESSION['reg']['login'], crypt::hash($_POST['password'], $dcms->salt), $sex, $ank_dr, $ank_mr, $ank_gr));
            $id_user = $db->lastInsertId();

            if ($id_user && is_numeric($id_user)) {

                if ($susp = is_valid::suspicion($_SESSION['reg']['login'])) {
                    // подозрительный логин
                    $res = $db->prepare("INSERT INTO `users_suspicion` (`id_user`, `text`) VALUES (?, ?)");
                    $res->execute(Array($id_user, $susp));
                    $dcms->distribution("Пользователь [user]{$id_user}[/user] сочтен подозрительным, так как в нике была обнаружена несвязная комбинация символов: {$susp}\n[url=/dpanel/users.suspicious.php]Список подозрительных пользователей[/url]", 4);
                }

                $step = 3;
            } else {
                $doc->err(__('Ошибка при регистрации. Попробуйте позже'));
                $step = 1;
            }
        }
    }
}

if ($step == 3) {
    $doc->msg(__('Вы успешно зарегистрированы'));

    if ($dcms->reg_with_mail && !$inv) {
        echo __("На ваш E-mail отправлено письмо с ссылкой для активации аккаунта");
    } else {
        echo __("Теперь Вы можете <a href='%s'>Авторизоваться</a>", '/login.php' . (isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : null));
    }

    unset($_SESSION['reg']);
    header('Refresh: 1; url=/login.php');
    exit;
}

if ($step == 2) {
    $doc->title = __('Завершение регистрации'); // заголовок страницы

    $form = new form(new url(null, array('step' => 'final')));
    $form->bbcode(__('Ваш логин: %s', '[b]' . $login . '[/b]'));
    $form->password('password', __('Пароль') . ' [6-32]');
    $form->password('password_retry', __('Повторите пароль'));

    $d_r = array();
    $m_r = array();
    $g_r = array();

    for ($i = 1; $i <= 31; $i++) {
        $d_r [] = array($i, $i);
    }
    for ($i = 1; $i <= 12; $i++) {
        $m_r [] = array($i, misc::getLocaleMonth($i));
    }
    for ($i = (date('Y') - 5); $i >= (date('Y') - 90); $i--) {
        $g_r [] = array($i, $i);
    }

    $form->bbcode(__('Дата рождения') . ':');
    $form->block('<div class="fields">');
    $form->select('ank_d_r', false, $d_r, false);
    $form->select('ank_m_r', false, $m_r, false);
    $form->select('ank_g_r', false, $g_r, true);
    $form->block('</div>');

    $form->select('sex', __('Ваш пол'), array(array(1, __('Мужской')), array(0, __('Женский'))));

    if ($dcms->reg_with_mail && !$inv) {
        $form->text('mail', __('Ваш E-mail') . '*');
        $form->bbcode('* ' . __('На Ваш E-mail придет письмо с ссылкой для активации аккаунта'));
    }
    $form->captcha();
    $form->button(__('Зарегистрироваться'), 'post');
    $form->display();
    exit;
}

if ($step == 1) {
    $doc->title = __('Подбор логина'); // заголовок страницы

    $form = new form(new url(null, array('step' => 'nick')));
    $form->text('login', __('Выберите логин') . ' [A-zА-я0-9 -_]');
    $form->bbcode('- ' . __('Сочетание русского и английского алфавитов запрещено'));
    $form->bbcode('- ' . __('Использование пробелов вначале и конце строк запрещено'));
    $form->bbcode('- ' . __('Логин не должен начинаться с цифр'));
    $form->button(__('Продолжить'), 'post');
    $form->display();
    exit;
}

if ($step == 0) {
    $doc->title = __('Соглашение'); // заголовок страницы
    $form = new form(new url(null, array('step' => 'rules')));
    $form->bbcode(@file_get_contents(H . '/sys/docs/rules.txt'));
    $form->block('<input type="submit" name="ok" value="' . __('Принимаю') . '" class="tiny ui blue button" />');
    $form->block('<input type="submit" name="no" value="' . __('Не принимаю') . '" class="tiny ui blue button" />');
    $form->display();


    if ($dcms->vk_auth_enable && $dcms->vk_app_id && $dcms->vk_app_secret) {
        $vk = new vk($dcms->vk_app_id, $dcms->vk_app_secret);
        $form = new form($vk->getAuthorizationUri('http://' . $_SERVER['HTTP_HOST'] . '/vk.php', 'email'));
        $form->button(__('Регистрация через VK'));
        $form->display();
    }
    exit;
}