<?php

include_once '../sys/inc/start.php';

if (!empty($_GET['theme']) && themes::exists($_GET['theme'])) {
    $probe_theme = $_GET['theme'];
}

$doc = new document(1);
$doc->title = __('Тема оформления');


if (!empty($probe_theme)) {
    $doc->ret(__('Список тем оформления'), '?');
    $doc->ret(__('Личное меню'), '/menu.user.php');

    $theme = themes::getThemeByName($probe_theme);

    if (isset($_POST['save'])) {
        $user->theme = $probe_theme;
        $doc->msg('Тема оформления успешно изменена');
        exit;
    }

    if (isset($_POST['cancel'])) {
        header('Location: ?' . SID);
        exit;
    }

    $form = new form(new url());
    $form->bbcode(__('Вы действительно хотите применить тему оформления "%s" для браузеров типа "%s"?', $theme->getName(), $dcms->browser_type));
    $form->button(__('Сохранить'), 'save', false);
    $form->button(__('Отмена'), 'cancel');
    $form->display();
    exit;
}


$themes_list = themes::getAllThemes();
$listing = new listing();
foreach ($themes_list as $theme) {
    $post = $listing->post();
    $post->icon('html5');
    $post->title = $theme->getViewName();
    $post->highlight = $user->theme == $theme->getName();
    $post->url = '?theme=' . urlencode($theme->getName());
    $supported = $theme->browserSupport($dcms->browser_type);
    $post->content[] = __('Поддерживаемые типы браузеров: %s', implode(', ', $theme->getBrowsers()));
    if (!$supported)
        $post->content[] = '[b]' . __('Тема может некорректно отображаться на Вашем устройстве') . '[/b]';
}

$listing->display(__('Список тем оформления пуст'));

$doc->ret(__('Личное меню'), '/menu.user.php');
