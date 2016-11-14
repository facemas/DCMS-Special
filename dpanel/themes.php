<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(6);

$doc->ret(__('Управление'), '/dpanel/');
$doc->title = __('Темы оформления');

$dcms = dcms::getInstance();
$themes = themes::getAllThemes();

$list = new listing();
foreach ($themes AS $theme) {
    $post = $list->post();
    $post->title = $theme->getViewName();
    $post->icon('html5');

    if (is_file(H . '/sys/themes/' . $theme->getName() . '/settings.php')) {
        $post->action('settings', 'theme.settings.php?theme=' . urlencode($theme->getName()));
    }
    $post->content[] = __('Поддерживаемые типы браузеров: %s', join(', ', $theme->getBrowsers()));

    $sections = $theme->getSections();
    $echo_section = $theme->getEchoSectionKey();
    $post->content[] = '[b]' . __('Секция основного вывода: %s (%s)', $echo_section, $sections[$echo_section]) . '[/b]';

    if (count($sections) > 1) {
        $post->action('widget', 'theme.widgets.php?theme=' . urlencode($theme->getName()));
    }

    foreach ($sections AS $section_key => $section_name) {
        if ($section_key === $echo_section) {
            continue;
        }
        $widgets = $theme->getWidgets($section_key);
        $widgets_names = array();
        foreach ($widgets AS $widget_name) {
            $widget = widgets::getWidgetByName($widget_name);
            if ($widget) {
                $widgets_names[] = $widget->getViewName();
            }
        }
        $post->content[] = __("Секция %s (%s). Виджеты: %s", $section_key, $section_name, $widgets_names ? join(', ', $widgets_names) : '[i]' . __('отсутствуют') . '[/i]');
    }

    switch ($theme->getName()) {
        case $dcms->theme_light:
            $post->content[] = '[b]' . __("По умолчанию для браузеров мобильных телефонов") . '[/b]';
            break;
        case $dcms->theme_mobile:
            $post->content[] = '[b]' . __("По умолчанию для браузеров смартфонов") . '[/b]';
            break;
        case $dcms->theme_full:
            $post->content[] = '[b]' . __("По умолчанию для WEB браузеров") . '[/b]';
            break;
    }
}
$list->display();
