<?php

$ank = (empty($_GET ['id'])) ? $user : new user((int) $_GET ['id']);

$from = 'anketa';
$doc->tab(__('Активность'), '?act=activity&amp;id=' . $ank->id, $from === 'activity');
$doc->tab(__('Анкета'), '?act=anketa&amp;id=' . $ank->id, $from === 'anketa');
$doc->tab(__('Основное'), '?id=' . $ank->id, $from === 'default');

if ($ank->realname) {
    $name = ($ank->lastname && $ank->middle_n) ? "$ank->lastname $ank->realname $ank->middle_n" : $ank->realname . ($ank->middle_n ? " " . $ank->middle_n : '') . ($ank->lastname ? " " . $ank->lastname : '');
    $post = $listing->post();
    $post->title = ($ank->lastname && $ank->middle_n) ? __('ФИО') : __('Имя');
    $post->content[] = $name;
}
//endregion
//region Дата рождения
if ($ank->ank_d_r && $ank->ank_m_r && $ank->ank_g_r) {
    $post = $listing->post();
    $post->title = __('Дата рождения');
    $post->content = $ank->ank_d_r . ' ' . misc::getLocaleMonth($ank->ank_m_r) . ' ' . $ank->ank_g_r;

    $post = $listing->post();
    $post->title = __('Возраст');
    $post->content = misc::get_age($ank->ank_g_r, $ank->ank_m_r, $ank->ank_d_r, true);
} elseif ($ank->ank_d_r && $ank->ank_m_r) {

    $post = $listing->post();
    $post->title = __('День рождения');
    $post->content = $ank->ank_d_r . ' ' . misc::getLocaleMonth($ank->ank_m_r);
}

if ($ank->language || $ank->languages) { // $ank->language(s)
    $post = $listing->post(); // Новый блок
    $post->icon('language'); // Пиктограмка пункта: иероглиф
    $post->title = ($ank->languages) ? __('Языки') : __('Язык');
    $post->content = $ank->languages ? $ank->languages : $ank->language;
}

if ($ank->skype) {
    if ($ank->is_friend($user) || $ank->vis_skype) {

        $post = $listing->post();
        $post->title = 'Skype';
        $post->content = $ank->skype;
        $post->url = 'skype:' . $ank->skype . '?chat';
    } else {

        $post = $listing->post();
        $post->title = 'Skype';
        $post->url = '/faq.php?info=hide&amp;return=' . URL;
        $post->content = __('Информация скрыта');
    }
}
//endregion
//region E-mail
if ($ank->email) {
    if ($ank->is_friend($user) || $ank->vis_email) {
        $post = $listing->post();
        $post->title = 'E-mail';
        $post->content = $ank->email;
        if (preg_match("#\@(mail|bk|inbox|list)\.ru$#i", $ank->email))
            $post->icon = 'http://status.mail.ru/?' . $ank->email;
        $post->url = 'mailto:' . $ank->email;
    } else {
        $post = $listing->post();
        $post->title = 'E-mail';
        $post->url = '/faq.php?info=hide&amp;return=' . URL;
        $post->content = __('Информация скрыта');
    }
}
//endregion
//region Регистрационный E-mail
if ($ank->reg_mail) {
    if ($user->group > $ank->group) {
        $post = $listing->post();
        $post->title = __('Регистрационный E-mail');
        $post->content = $ank->reg_mail;
        if (preg_match("#\@(mail|bk|inbox|list)\.ru$#i", $ank->reg_mail))
            $post->icon = 'http://status.mail.ru/?' . $ank->reg_mail;
        $post->url = 'mailto:' . $ank->reg_mail;
    }
}

// О себе
if ($ank->description) {
    $post = $listing->post();
    $post->title = __('О себе');
    $post->content[] = $ank->description;
}

