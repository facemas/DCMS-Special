<?php

defined('SOCCMS') or die();

/**
 * Преобразуем $_FILES в более удобный массив
 * @param $name
 * @return array
 */
function files_to_normal_array($name) {
    $files = array();
    if (!empty($_FILES [$name])) {
        foreach ($_FILES [$name] AS $key => $value) {
            $value = (array) $value;
            foreach ($value AS $index => $val) {
                $files[$index][$key] = $val;
            }
        }
    }
    return $files;
}

// файл отвечает за исполнение действий
if ($access_write) {

    // выгрузка
    if (!empty($_FILES ['files'])) {
        $files = files_to_normal_array('files');
        foreach ($files AS $file) {
            if ($file['error']) {
                switch ($file['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $doc->err(__('Размер принятого файла превысил максимально допустимый размер, который задан директивой upload_max_filesize'));
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $doc->err(__('Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме'));
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $doc->err(__('Загружаемый файл был получен только частично'));
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $doc->err(__('Файл не был загружен'));
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $doc->err(__('Отсутствует временная папка'));
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $doc->err(__('Не удалось записать файл на диск'));
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $doc->err(__('PHP-расширение остановило загрузку файла'));
                        break;
                }
                continue;
            }

            if (!$file['size']) {
                $doc->err(__('Содержимое файла пусто'));
                continue;
            }

            if ($dir->is_file(text::for_filename($file ['name']))) {
                $doc->err(__('Файл с таким названием (%s) уже существует', text::for_filename($file ['name'])));
                continue;
            }

            if ($files_ok = $dir->filesAdd(array($file ['tmp_name'] => $file ['name']))) {
                $files_ok [$file ['tmp_name']]->id_user = $user->id;
                $files_ok [$file ['tmp_name']]->group_edit = max($user->group, $dir->group_write, 2);
                $user->balls += $dcms->add_balls_upload_file;
                $doc->msg(__('Файл "%s" успешно добавлен', $file ['name']));
                // записываем свое действие в общий лог
                if ($dir->group_write > 1) {
                    $dcms->log('Файлы', 'Выгрузка файла [url="/files' . $files_ok [$file['tmp_name']]->getPath() . '"]' . $file ['name'] . '[/url]');
                }
                unset($files_ok);
            } else {
                $doc->err(__('Не удалось сохранить выгруженный файл'));
            }
        }
    }
}

if ($access_edit) {
    // импорт файлов
    if (!empty($_POST['file_import']) && !empty($_POST['url'])) {
        if ($file = $dir->fileImport($_POST ['url'])) {
            $doc->msg(__('Файл "%s" успешно импортирован', $file->runame));
            $file->id_user = $user->id;
            // записываем свое действие в общий лог
            if ($dir->group_write > 1) {
                $dcms->log('Файлы', 'Импорт файла [url="/files' . $file->getPath() . '"]' . $file->runame . '[/url]');
            }

            header('Refresh: 1; url=?act=file_import&' . passgen());

            $doc->act(__('К файлу'), '/files' . $file->getPath() . '.htm');
            $doc->act(__('Вернуться'), '?act=file_import&amp;' . passgen());
            exit();
        } elseif ($error = $dir->error) {
            $doc->err($error);
        } else {
            $doc->err(__('Не удалось импортировать файл'));
        }
    }
    // изменеение параметров
    if (isset($_POST ['edit_path']) && !empty($_POST ['path_rel_new'])) {
        // перемещение папки
        $root_dir = new files(FILES);
        $dirs = $root_dir->getPathesRecurse($dir);

        $path_rel_new = $_POST ['path_rel_new'];

        foreach ($dirs as $dir2) {
            // если нет прав на чтение папки или на запись в папку, то пропускаем
            if ($dir2->group_show > $user->group || $dir2->group_write > $user->group) {
                continue;
            }
            // мы не можем папку переместить саму в себя
            if ($dir2->path_rel == $dir->path_rel)
                continue;
            if ($dir2->getPath() == $path_rel_new) {
                $path_abs_new = $dir2->path_abs . '/' . $dir->name;
            }
        }

        if (!empty($path_abs_new)) {
            if ($dir->move($path_abs_new)) {
                // записываем свое действие в общий лог
                $dcms->log('Файлы', 'Перемещение папки [url="/files' . $dir->getPath() . '"]' . $dir->runame . '[/url]');

                $doc->msg(__('Папка успешно перемещена'));
                $doc->act(__('Вернуться'), '/files' . $dir->getPath() . '?' . passgen());
                header('Refresh: 1; url=/files' . $dir->getPath() . '?' . passgen());
                exit();
            } else {
                $doc->err(__('При перемещении папки возникла ошибка'));
            }
        } else {
            $doc->err(__('Ошибка при выборе нового каталога'));
        }
    }

    if (isset($_POST ['write_dir']) && isset($_POST ['name'])) {
        $runame = text::for_name($_POST ['name']);

        if (!$runame) {
            $doc->err(__('Неверно задано имя папки'));
        } elseif (!$new_dir = $dir->mkdir($runame)) {
            $doc->err(__('Не удалось создать папку на сервере'));
        } else {
            $new_dir->id_user = $user->id;

            $dcms->log('Файлы', 'Создание папки [url="/files' . $new_dir->getPath() . '"]' . $new_dir->runame . '[/url]');

            $doc->msg(__('Папка "%s" успешно создана', $runame));
            $doc->act(__('Вернуться'), '?act=write_dir');
            header('Refresh: 1; url=?act=write_dir');
            exit();
        }
    }

    if (isset($_POST['edit_unlink']) && $rel_path && $dir->name{0} !== '.') {
        if ($dir->delete()) {
            $dcms->log('Файлы', 'Удаление папки ' . $dir->runame . ' (' . $dir->getPath() . ')');

            $doc->msg(__('Папка успешно удалена'));
            $doc->act(__('Вернуться'), '../?' . passgen());
            header('Refresh: 1; url=../?' . passgen());
            exit();
        } else {
            $doc->err(__('Ошибка при удалении папки'));
        }
    }

    if (isset($_POST ['edit_prop'])) {
        $groups = groups::load_ini(); // загружаем массив групп

        if ($rel_path && isset($_POST['position'])) {
            $dir->position = (int) $_POST['position'];
        }
        if (isset($_POST ['description'])) {
            $dir->description = text::input_text($_POST['description']);
        }
        if (isset($_POST ['meta_description'])) {
            $dir->meta_description = text::input_text($_POST['meta_description']);
        }
        if (isset($_POST ['meta_keywords'])) {
            $dir->meta_keywords = text::input_text($_POST['meta_keywords']);
        }


        $order_keys = $dir->getKeys();
        if (!empty($_POST ['sort_default']) && isset($order_keys [$_POST ['sort_default']])) {
            $dir->sort_default = $_POST ['sort_default'];
        }


        if (!empty($_POST ['name'])) {
            $runame = text::for_name($_POST ['name']);
            $name = text::for_filename($runame);

            if ($runame != $dir->runame) {
                $oldname = $dir->runame;
                if (!$runame || !$name) {
                    $doc->err(__('Неверно задано имя папки'));
                } elseif (!$dir->rename($runame, $name)) {
                    $doc->err(__('Не удалось переименовать папку'));
                } else {
                    $dcms->log('Файлы', 'Переименование папки из ' . $oldname . ' в [url="/files' . $dir->getPath() . '"]' . $dir->runame . '[/url]');
                    $doc->msg(__('Новое название папки "%s"', $runame));
                }
            }
        }

        if (isset($_POST ['group_show'])) { // просмотр
            $group_show = (int) $_POST ['group_show'];
            if (isset($groups [$group_show]) && $group_show != $dir->group_show) {
                $dir->setGroupShowRecurse($group_show); // данный параметр необходимо применять рекурсивно


                $dcms->log('Файлы', 'Изменение привилегий просмотра папки [url="/files' . $dir->getPath() . '"]' . $dir->runame . '[/url] на группу ' . groups::name($group_show));

                $doc->msg(__('Просмотр папки разрешен группе "%s" и выше', groups::name($group_show)));
            }
        }

        if (isset($_POST ['group_write'])) { // запись
            $group_write = (int) $_POST ['group_write'];
            if (isset($groups [$group_write]) && $group_write != $dir->group_write) {
                if ($dir->group_show > $group_write) {
                    $doc->err(__('Для того, чтобы выгружать файлы группе "%s" сначала необходимо дать права на просмотр этой папки', groups::name($group_write)));
                } else {
                    $dir->group_write = $group_write;

                    $dcms->log('Файлы', 'Изменение привилегий выгрузки файлов для папки [url="/files' . $dir->getPath() . '"]' . $dir->runame . '[/url] на группу ' . groups::name($group_write));

                    $doc->msg(__('Выгружать файлы разрешено группе "%s" и выше', groups::name($group_write)));
                }
            }
        }

        if (isset($_POST ['group_edit'])) { // редактирование
            $group_edit = (int) $_POST ['group_edit'];
            if (isset($groups [$group_edit]) && $group_edit != $dir->group_edit) {
                if ($dir->group_write > $group_edit) {
                    $doc->err(__('Для изменения параметров папки и создания папок группе "%s" сначала необходимо дать права на запись в папку', groups::name($group_edit)));
                } else {
                    $dir->group_edit = $group_edit;

                    $dcms->log('Файлы', 'Изменение привилегий создания папок и изменения параметров для папки [url="/files' . $dir->getPath() . '"]' . $dir->runame . '[/url] на группу ' . groups::name($group_edit));

                    $doc->msg(__('Изменять параметры папки и создание папок теперь разрешено группе "%s" и выше', groups::name($group_edit)));
                }
            }
        }

        $doc->msg(__('Изменения сохранены'));
        $doc->act(__('Вернуться'), '/files' . $dir->getPath() . '?' . passgen());
        header('Refresh: 2; url=/files' . $dir->getPath() . '?' . passgen());
        exit;
    }
}