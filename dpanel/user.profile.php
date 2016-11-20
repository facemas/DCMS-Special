<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$groups = groups::load_ini();
$doc = new document(4);
$doc->title = __('Профиль');

$browser_types = array('light', 'mobile', 'full');

if (isset($_GET ['id_ank'])) {
    $ank = new user($_GET ['id_ank']);
} else {
    $ank = $user;
}

if (!$ank->group) {
    $doc->toReturn();
    $doc->err(__('Не удалось загрузить данные пользователя'));
    exit();
}

$doc->title .= ' "' . $ank->login . '"';

if ($ank->group >= $user->group) {
    $doc->toReturn();
    $doc->err(__('Ваш статус не позволяет производить действия с данным пользователем'));
    exit();
}

if (isset($_POST['save'])) {
    $ank->realname = text::for_name(@$_POST['realname']);
    $ank->lastname = text::for_name(@$_POST['lastname']);
    $ank->middle_n = text::for_name(@$_POST['middle_n']);
    $ank->balls = abs((int) @$_POST['balls']);

    if (isset($_POST['ank_d_r'])) {
        $ank_d_r = (int) $_POST['ank_d_r'];
        if ($ank_d_r >= 1 && $ank_d_r <= 31) {
            $ank->ank_d_r = $ank_d_r;
        } else {
            $doc->err(__('Не корректный формат дня рождения'));
        }
    }

    if (isset($_POST['ank_m_r'])) {
        $ank_m_r = (int) $_POST['ank_m_r'];
        if ($ank_m_r >= 1 && $ank_m_r <= 12) {
            $ank->ank_m_r = $ank_m_r;
        } else {
            $doc->err(__('Не корректный формат месяца рождения'));
        }
    }

    if (isset($_POST['ank_g_r'])) {
        $ank_g_r = (int) $_POST['ank_g_r'];
        if ($ank_g_r >= date('Y') - 100 && $ank_g_r <= date('Y')) {
            $ank->ank_g_r = $ank_g_r;
        } else {
            $doc->err(__('Не корректный формат года рождения'));
        }
    }

    if (!empty($_POST ['skype'])) {
        if (!is_valid::skype($_POST ['skype'])) {
            $doc->err(__('Указан не корректный логин Skype'));
        } else {
            $ank->skype = $_POST ['skype'];
        }
    }

    if (!empty($_POST ['email'])) {
        if (!is_valid::mail($_POST ['email'])) {
            $doc->err(__('Указан не корректный %s', 'E-Mail'));
        } else {
            $ank->email = $_POST ['email'];
        }
    }
    if (!empty($_POST['reg_mail'])) {
        if (!is_valid::mail($_POST ['reg_mail'])) {
            $doc->err(__('Указан не корректный %s', 'Primary E-mail'));
        } else {
            $ank->reg_mail = $_POST ['reg_mail'];
        }
    }

    foreach ($browser_types as $type) {
        $t = "items_per_page_$type";
        // количество пунктов на страницу
        if (!empty($_POST [$t])) {
            $ipp = (int) $_POST [$t];
            if ($ipp >= 5 && $ipp <= 99) {
                $ank->$t = $ipp;
            } else {
                $doc->err(__('Недопустимое количество пунктов на страницу'));
            }
        }

        $t = "theme_$type";

        if (!empty($_POST [$t])) {
            $theme_set = (string) $_POST [$t];

            if (themes::exists($theme_set, $type)) {
                $ank->$t = $theme_set;
            }
        }
    }
    // временной сдвиг
    if (!empty($_POST ['time_shift'])) {
        $ipp = (int) $_POST ['time_shift'];
        if ($ipp >= -12 && $ipp <= 12) {
            $ank->time_shift = $ipp;
        } else {
            $doc->err(__('Недопустимое время'));
        }
    }

    $ank->vis_email = (int) !empty($_POST ['vis_email']);
    $ank->vis_verify = (int) !empty($_POST ['vis_verify']);
    $ank->vis_friends = (int) !empty($_POST ['vis_friends']);
    $ank->vis_skype = (int) !empty($_POST ['vis_skype']);

    $dcms->log('Пользователи', 'Изменение профиля пользователя [url=/profile.view.php?id=' . $ank->id . ']' . $ank->login . '[/url]');

    $doc->msg(__('Изменения сохранены'));
}


$form = new form(new url());

foreach ($browser_types as $type) {
    $t = "items_per_page_$type";
    $form->text($t, __('Пунктов на страницу') . ' (' . $type . ') [5-99]', $ank->$t);
}

foreach ($browser_types as $b_type) {
    $t = 'theme_' . $b_type;
    $options = array(); // темы оформления для light браузера
    $themes_list = themes::getThemesByType($b_type); // только для определенного типа браузера
    foreach ($themes_list as $theme) {
        $options [] = array($theme->getName(), $theme->getViewName(), $ank->$t === $theme->getName());
    }
    $form->select($t, __('Тема оформления') . ' (' . strtoupper($b_type) . ')', $options);
}

$options = array(); // Врменной сдвиг
for ($i = -12; $i < 12; $i++) {
    $options [] = array($i, date('G:i', TIME + $i * 60 * 60), $ank->time_shift == $i);
}
$form->select('time_shift', __('Время'), $options);

$form->text('realname', __('Имя'), $ank->realname);
$form->text('lastname', __('Фамилия'), $ank->lastname);
$form->text('middle_n', __('Отчество'), $ank->middle_n);

$d_r = array();
$m_r = array();
$g_r = array();

for ($i = 1; $i <= 31; $i++) {
    $d_r [] = array($i, $i, $ank->ank_d_r == $i);
}
for ($i = 1; $i <= 12; $i++) {
    $m_r [] = array($i, misc::getLocaleMonth($i), $ank->ank_m_r == $i);
}
for ($i = (date('Y') - 5); $i >= (date('Y') - 90); $i--) {
    $g_r [] = array($i, $i, $ank->ank_g_r == $i);
}

$form->bbcode(__('Дата рождения') . ':');
$form->block("<div class='fields'>");
$form->select('ank_d_r', false, $d_r, false);
$form->select('ank_m_r', false, $m_r, false);
$form->select('ank_g_r', false, $g_r, true);
$form->block("</div>");
$form->text('balls', __('Баллы'), $ank->balls);
$form->text('skype', 'Skype', $ank->skype);
$form->text('reg_mail', __('Приватный E-mail'), $ank->reg_mail);
$form->text('email', 'E-mail', $ank->email);

$form->checkbox('vis_skype', __('Показывать %s', 'Skype'), $ank->vis_skype);
$form->checkbox('vis_email', __('Показывать %s', 'E-Mail'), $ank->vis_email);
$form->checkbox('vis_friends', __('Отображать список друзей'), $ank->vis_friends);
$form->checkbox('vis_verify', __('Верификация страницы'), $ank->vis_verify);

$form->block("<br />");
$form->button(__('Сохранить'), 'save', false, 'tiny ui green labeled fa button', 'fa fa-save fa-fw');
$form->display();

$doc->ret(__('Действия'), 'user.actions.php?id=' . $ank->id);
$doc->ret(__('Анкета "%s"', $ank->login), '/profile.view.php?id=' . $ank->id);
$doc->ret(__('Управление'), '/dpanel/');
