<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$smiles = smiles::get_ini();
$doc = new document(5);
$doc->title = __('Смайлы');

$smiles_a = array();
// загружаем список смайлов
$smiles_gl = (array) glob(H . '/sys/images/smiles/*.gif');

foreach ($smiles_gl as $path) {
    preg_match('#/([^/]+)\.gif$#', $path, $m);
    $smiles_a[$m[1]] = $path;
}
if (!empty($_GET['delete']) && isset($smiles_a[$_GET['delete']])) {
    $sm = $_GET['delete'];
    $phrases = array_keys($smiles, $sm);
    foreach ($phrases as $phrase) {
        unset($smiles[$phrase]);
    }
    if (!unlink(H . '/sys/images/smiles/' . $sm . '.gif')) {
        $doc->err(__('Смайл %s не найден', $sm . '.gif'));
    } elseif (!ini::save(H . '/sys/ini/smiles.ini', $smiles)) {
        $doc->err(__('Нет прав на запись в файл %s', 'smiles.ini'));
    } else {
        $doc->msg(__('Смайл "%s" успешно удален', $sm));
    }
}
if (!empty($_GET['smile']) && isset($smiles_a[$_GET['smile']])) {
    $sm = $_GET['smile'];

    if (isset($_GET['act']) && $_GET['act'] == 'delete' && !empty($_GET['phrase'])) {
        $phrase = (string) $_GET['phrase'];
        if (!empty($smiles[$phrase])) {
            if ($smiles[$phrase] != $sm)
                $doc->err(__('Фраза относится к другому смайлу'));
            else {
                unset($smiles[$phrase]);

                if (ini::save(H . '/sys/ini/smiles.ini', $smiles)) {
                    $doc->msg(__('Фраза успешно удалена'));
                } else
                    $doc->err(__('Нет прав на запись в файл %s', 'smiles.ini'));
            }
        }else {
            $doc->err(__('Фраза уже удалена'));
        }
    }

    if (!empty($_POST['phrase'])) {
        $phrase = text::toValue(preg_replace('#(^\.)|[^a-z0-9а-я_\-\.]+#ui', '', $_POST['phrase']));
        if ($phrase) {
            if ($phrase == 'null' || $phrase == 'yes' || $phrase == 'no' || $phrase == 'true' || $phrase == 'false')
                $doc->err(__('Запрещено использовать данную фразу'));
            elseif (!empty($smiles[$phrase]))
                $doc->err(__('Данная фраза используется для смайла "%s"', $smiles[$phrase]));
            else {
                $smiles[$phrase] = $sm;
                if (ini::save(H . '/sys/ini/smiles.ini', $smiles)) {
                    $doc->msg(__('Фраза успешно добавлена'));
                } else
                    $doc->err(__('Нет прав на запись в файл %s', 'smiles.ini'));
            }
        }else {
            $doc->err(__('Запрещено использование спец.символов'));
        }
    }

    $doc->title = __('Смайл "%s"', $sm);

    $phrases = array_keys($smiles, $sm);

    $listing = new listing();
    foreach ($phrases as $text) {
        $post = $listing->post();
        $post->title = $text;
        $post->image = '/sys/images/smiles/' . $sm . '.gif';
        $post->action('delete', '?smile=' . urlencode($sm) . '&amp;phrase=' . urlencode($text) . '&amp;act=delete');
    }

    $listing->display(__('Фразы отсутствуют'));

    $form = new form(new url());
    $form->text('phrase', __('Фраза'));
    $form->button(__('Добавить'));
    $form->display();

    $doc->ret(__('Смайлы'), '?');
    $doc->ret(__('Управление'), './');
    exit;
}

if (isset($_GET['add'])) {
    /**
     * Выгрузка смайла
     */
    if (isset($_POST['upload'])) {
        if ($_FILES['file']['error'])
            $doc->err(__('Ошибка при загрузке'));
        elseif (!$_FILES['file']['size']) {
            $doc->err(__('Содержимое файла пусто'));
        } else {
            $smile = @imagecreatefromgif($_FILES['file']['tmp_name']);
            if (!$smile) {
                $doc->err(__('Не верный формат'));
            } elseif (file_exists(H . '/sys/images/smiles/' . $_FILES['file']['name'])) {
                $doc->err(__('Такой смайл уже существует'));
            } elseif (move_uploaded_file($_FILES['file']['tmp_name'], H . '/sys/images/smiles/' . $_FILES['file']['name'])) {
                $name = explode('.', text::for_filename($_FILES['file']['name']));
                $doc->msg(__('Смайл "%s" успешно добавлен', $_FILES['file']['name']));
                header('Refresh: 1; ?smile=' . $name[0]);
                exit;
            }
        }
    }
    /**
     * Импорт смайла
     */
    if (isset($_POST['import'])) {
        $url = text::input_text($_POST['url']);
        $purl = parse_url($url);
        $smile = @imagecreatefromgif($url);
        if (!$smile) {
            $doc->err(__('Не верный формат'));
        } elseif (empty($purl['path'])) {
            $doc->err(__('Путь к файлу не распознан'));
        } elseif (!$fname = basename($purl['path'])) {
            $doc->err(__('Не удалось получить имя файла из пути'));
        } elseif (file_exists(H . '/sys/images/smiles/' . text::for_filename($fname))) {
            $doc->err(__('Такой смайл уже существует'));
        } elseif (copy($url, H . '/sys/images/smiles/' . text::for_filename($fname))) {
            $name = explode('.', text::for_filename($fname));
            $doc->msg(__('Смайл "%s" успешно добавлен', text::for_filename($fname)));
            header('Refresh: 1; ?smile=' . $name[0]);
            exit;
        }
    }

    $doc->title = __('Добавление смайла');

    $form = new form('?add&amp;' . passgen());
    $form->file('file', __('Смайл (.gif)'));
    $form->text('url', __('URL'));
    $form->button(__('Выгрузить'), 'upload', false);
    $form->button(__('Импортировать'), 'import');
    $form->display();

    $doc->ret(__('Смайлы'), '?');
    $doc->ret(__('Управление'), './');
    exit;
}
$listing = new listing();
foreach ($smiles_a as $name => $path) {
    $post = $listing->post();
    $post->image = '/sys/images/smiles/' . $name . '.gif';
    $post->setUrl(new url(null, array('smile' => $name)));
    $post->content = __('Варианты') . ': ' . implode(', ', array_keys($smiles, $name));
    $post->action('delete', '?delete=' . $name);
}
$listing->display(__('Смайлы отсутствуют'));
$doc->opt(__('Добавить'), '?add', false, '<i class="fa fa-plus fa-fw"></i>');
$doc->ret(__('Управление'), './');
