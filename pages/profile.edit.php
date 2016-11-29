<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Мой профиль');

if (isset($_POST['save'])) {
    $user->languages = text::input_text(@$_POST['languages']);
    $user->lastname = text::for_name(@$_POST ['lastname']);
    $user->realname = text::for_name(@$_POST ['realname']);
    $user->middle_n = text::for_name(@$_POST ['middle_n']);

    if (isset($_POST['ank_d_r'])) {
        $ank_d_r = (int) $_POST ['ank_d_r'];

        if ($ank_d_r >= 1 && $ank_d_r <= 31) {
            $user->ank_d_r = $ank_d_r;
        } else {
            $doc->err(__('Не корректный формат дня рождения'));
        }
    }

    if (isset($_POST['ank_m_r'])) {
        $ank_m_r = (int) $_POST ['ank_m_r'];

        if ($ank_m_r >= 1 && $ank_m_r <= 12) {
            $user->ank_m_r = $ank_m_r;
        } else {
            $doc->err(__('Не корректный формат месяца рождения'));
        }
    }

    if (isset($_POST['ank_g_r'])) {
        $ank_g_r = (int) $_POST['ank_g_r'];

        if ($ank_g_r >= date('Y') - 100 && $ank_g_r <= date('Y')) {
            $user->ank_g_r = $ank_g_r;
        } else {
            $doc->err(__('Не корректный формат года рождения'));
        }
    }

    if (isset($_POST['skype'])) {
        if (empty($_POST['skype'])) {
            $user->skype = '';
        } elseif (!is_valid::skype($_POST['skype'])) {
            $doc->err(__('Указан не корректный %s', 'Skype login'));
        } else {
            $user->skype = $_POST['skype'];
        }
    }

    /* Использование если надо
      if (!empty($_POST ['wmid'])) {
      if ($user->wmid && $user->wmid != $_POST ['wmid']) {
      $doc->err(__('Активированный WMID изменять и удалять запрещено'));
      } elseif (!is_valid::wmid($_POST ['wmid'])) {
      $doc->err(__('Указан не корректный %s', 'WMID'));
      } elseif ($user->wmid != $_POST ['wmid']) {
      $user->wmid = $_POST ['wmid'];
      }
      }
     * 
     */

    if (isset($_POST['email'])) {
        if (empty($_POST['email'])) {
            $user->email = '';
        } elseif (!is_valid::mail($_POST ['email'])) {
            $doc->err(__('Указан не корректный %s', 'E-Mail'));
        } else {
            $user->email = $_POST['email'];
        }
    }

    $user->description = text::input_text(@$_POST ['description']);

    $doc->msg(__('Изменения сохранены'));
}

$form = new form('?' . passgen());
$form->text('lastname', __('Фамилия'), $user->lastname, false);
$form->text('realname', __('Имя'), $user->realname, false);
$form->text('middle_n', __('Отчество'), $user->middle_n, false);

$d_r = array();
$m_r = array();
$g_r = array();

for ($i = 1; $i <= 31; $i++) {
    $d_r [] = array($i, $i, $user->ank_d_r == $i);
}
for ($i = 1; $i <= 12; $i++) {
    $m_r [] = array($i, misc::getLocaleMonth($i), $user->ank_m_r == $i);
}
for ($i = (date('Y') - 5); $i >= (date('Y') - 90); $i--) {
    $g_r [] = array($i, $i, $user->ank_g_r == $i);
}

$form->bbcode('[b]' . __('Дата рождения') . '[/b]', FALSE);
$form->block('<div class="fields">');
$form->select('ank_d_r', false, $d_r, false);
$form->select('ank_m_r', false, $m_r, false);
$form->select('ank_g_r', false, $g_r, false);
$form->block('</div>');
$form->text('skype', 'Skype', $user->skype, false);
$form->text('email', 'E-Mail', $user->email, false);
//$form->text('wmid', 'WMID', $user->wmid);
$form->text('languages', __('Языки'), $user->languages ? $user->languages : $user_language_pack->name, false);
$form->textarea('description', __('О себе') . ' [512]', $user->description);

$form->button(__('Сохранить'), 'save', false, 'tiny ui green labeled fa button', 'fa fa-save fa-fw');
$form->display();

$doc->ret(__('Личное меню'), '/menu.user.php');
