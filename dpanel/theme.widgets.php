<?php
include_once '../sys/inc/start.php';
dpanel::check_access();

if (!empty($_GET['theme']) && themes::exists($_GET['theme'])) {
    $probe_theme = $_GET['theme'];
}

$doc = new document(6);
$doc->title = __('Виджеты темы оформления');
$doc->ret(__('Темы оформления'), 'themes.php');
$doc->ret(__('Админка'), '/dpanel/');


if (empty($_GET['theme']) || !themes::exists($_GET['theme'])) {
    $doc->err(__('Тема оформления не найдена'));
    exit;
}

$theme = themes::getThemeByName($_GET['theme']);

$doc->title = __('Виджеты темы оформления "%s"', $theme->getViewName());

$sections = $theme->getSections();
$echo_section_key = $theme->getEchoSectionKey();
$widgets = widgets::getAllWidgets();

$changed = false;

$form = new form(new url());
foreach ($sections AS $section_key => $section_name) {
    if ($section_key === $echo_section_key) {
        continue;
    }

    $form->bbcode('[b]' . __('Виджеты секции "%s"', $section_name) . ':[/b]');

    $section_widgets = $theme->getWidgets($section_key);
    foreach ($section_widgets AS $section_widget_name) {
        $section_widget = widgets::getWidgetByName($section_widget_name);
        if ($section_widget) {
            if (isset($_POST['save']) && empty($_POST[$section_key . '!' . $section_widget->getName()])) {
                $theme->removeWidget($section_key, $section_widget->getName());
                $changed = true;
            }
            $form->checkbox($section_key . '!' . $section_widget->getName(), $section_widget->getViewName(), true);
        }
    }

    foreach ($widgets AS $widget) {
        if (in_array($widget->getName(), $section_widgets)) {
            continue;
        }

        if (isset($_POST['save']) && !empty($_POST[$section_key . '!' . $widget->getName()])) {
            $theme->addWidget($section_key, $widget->getName());
            $changed = true;
        }
        $form->checkbox($section_key . '!' . $widget->getName(), $widget->getViewName(), false);
    }

    $form->bbcode("");
}
$form->button(__('Сохранить'), 'save');

if ($changed) {
    $doc->msg(__('Набор виджетов успешно изменен'));
    header('Refresh: 1; url=?theme=' . $theme->getName());
    exit;
} else {
    $form->display();
}
