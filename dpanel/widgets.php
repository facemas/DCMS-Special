<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$smiles = smiles::get_ini();

$doc = new document(5);

$doc->title = __('Виджеты главной страницы');
$doc->ret(__('Управление'), '/dpanel/');

// загружаем список виджетов из конфигурации
$widgets_conf = (array) ini::read(H . '/sys/ini/widgets.ini');
// производим поиск виджетов в соответствующей директории
$widgets = array();
$wod = opendir(H . '/sys/widgets');
while ($rd = readdir($wod)) {
    if ($rd {0} === '.') {
        continue;
    }
    if (!is_file(H . '/sys/widgets/' . $rd . '/config.ini')) {
        continue;
    }

    $widgets[$rd] = new widget(H . '/sys/widgets/' . $rd);
}
closedir($wod);

// проверяем список виджетов и удаляем отсутствующие
foreach ($widgets_conf as $widget => $show) {
    if (!isset($widgets[$widget])) {
        $widgets_conf_need_save = true; // необходимо сохранить список
        unset($widgets_conf[$widget]);
    }
}
// добавляем отсутствующие виджеты в список, но по-умолчанию делаем их неактивными
foreach ($widgets as $widget_name => $widget) {
    if (!isset($widgets_conf[$widget_name])) {
        $widgets_conf_need_save = true; // сохраним обновленный список
        $widgets_conf[$widget_name] = 0;
    }
}

if (!empty($widgets_conf_need_save)) {
    if (ini::save(H . '/sys/ini/widgets.ini', $widgets_conf)) {
        $doc->msg(__('Список виджетов успешно обновлен'));
    } else {
        $doc->err(__('Невозможно сохранить список виджетов'));
    }
    unset($widgets_conf_need_save);
}

if (isset($_GET['sortable'])) {
    $doc->clean();
    $sortable = explode(',', $_POST['sortable']);

    foreach ($sortable as $position => $key) {
        // echo "$position $key\n";
        arraypos::setPosition($widgets_conf, $key, $position + 1);
    }

    header('Content-type: application/json');
    if (ini::save(H . '/sys/ini/widgets.ini', $widgets_conf)) {
        echo json_encode(array('result' => 1, 'description' => __('Порядок виджетов успешно сохранен')));
    } else {
        echo json_encode(array('result' => 0, 'description' => __('Не удалось сохранить конфигурацию виждетов')));
    }
    exit;
}

if (!empty($_GET['widget']) && isset($widgets_conf[$_GET['widget']]) && !empty($_GET['act'])) {
    switch ($_GET['act']) {
        case 'up':
            if (misc::array_key_move($widgets_conf, $_GET['widget'], - 1)) {
                $widgets_conf_need_save = true;
                $doc->msg(__('Виджет "%s" успешно перемещен вверх', $widgets[$_GET['widget']]->runame));
            } else {
                $doc->err(__('Виджет "%s" уже находится вверху', $widgets[$_GET['widget']]->runame));
            }
            break;

        case 'down':
            if (misc::array_key_move($widgets_conf, $_GET['widget'], 1)) {
                $widgets_conf_need_save = true;
                $doc->msg(__('Виджет "%s" успешно перемещен вниз', $widgets[$_GET['widget']]->runame));
            } else {
                $doc->err(__('Виджет "%s" уже находится внизу', $widgets[$_GET['widget']]->runame));
            }
            $widgets_conf_need_save = true;
            break;

        case 'hide':
            $doc->msg(__('Виджет "%s" успешно скрыт', $widgets[$_GET['widget']]->runame));
            $widgets_conf[$_GET['widget']] = 0;
            $widgets_conf_need_save = true;
            break;

        case 'show':
            $doc->msg(__('Виджет "%s" будет отображаться', $widgets[$_GET['widget']]->runame));
            $widgets_conf[$_GET['widget']] = 1;
            $widgets_conf_need_save = true;
            break;
    }

    if (!empty($widgets_conf_need_save)) {
        if (ini::save(H . '/sys/ini/widgets.ini', $widgets_conf)) {
            $doc->msg(__('Изменения сохранены'));
        } else {
            $doc->err(__('Невозможно сохранить новый список виджетов'));
        }
        unset($widgets_conf_need_save);
    }

    $doc->ret('Вернуться', '?' . passgen());
    header('Refresh: 1; url=?' . passgen());
    exit;
}

$listing = new listing();

foreach ($widgets_conf as $name => $show) {
    $widget = $widgets[$name];

    $post = $listing->post();
    $post->icon('puzzle-piece');
    $post->title = $widget->runame;
    $post->id = urlencode($name);

    $post2 = array();
    if ($autor = $widget->autor) {
        $post2[] = __('Автор: %s', text::toValue($autor));
    }

    if ($version = $widget->version) {
        $post2[] = __('Версия: %s', text::toValue($version));
    }
    $post->content = implode("<br />\n", $post2);

    if ($show) {
        $post->action('eye-slash', '?widget=' . urlencode($name) . '&amp;act=hide');
    } else {
        $post->action('eye', '?widget=' . urlencode($name) . '&amp;act=show');
        $post->highlight = true;
    }

    $post->action('arrow-up', '?widget=' . urlencode($name) . '&amp;act=up');
    $post->action('arrow-down', '?widget=' . urlencode($name) . '&amp;act=down');
}

$listing->sortable = '?sortable';
$listing->display(__('Нет результатов'));
