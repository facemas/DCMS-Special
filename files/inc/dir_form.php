<?php

defined('SOCCMS') or die();
// файл отвечает за отображение возможных действий
if ($access_write) {
    // выгрузка и импорт файлов
    switch (@$_GET ['act']) {
        case 'file_upload' : {
                $max_file_uploads = ini_get('max_file_uploads');
                $upload_max_filesize = misc::returnBytes(ini_get('upload_max_filesize'));
                $post_max_size = misc::returnBytes(ini_get('post_max_size'));
                $memory_limit = misc::returnBytes(ini_get('memory_limit'));

                if ($memory_limit > 0) {
                    $limit_size = min($upload_max_filesize, $post_max_size, $memory_limit);
                } else { // локально может отсутствовать лимит по памяти
                    $limit_size = min($upload_max_filesize, $post_max_size);
                }

                $form = new form('?' . passgen());
                $form->fileMultiple('files[]', __('Файлы (мультивыбор)'));
                $form->bbcode(__('Максимальный размер всех файлов не должен превышать %s', misc::getDataCapacity($limit_size)));
                $form->bbcode(__('Максимальное кол-во файлов: %s', $max_file_uploads));
                $form->bbcode(__('[b]Данные ограничения настраиваются администратором сервера[b]'));
                $form->button(__('Выгрузить'));
                $form->display();
            }
            break;
    }

    $doc->opt(__('Добавить файл'), '?act=file_upload', false, '<i class="fa fa-upload fa-fw"></i>');
}

if ($access_edit) {
    // изменеение параметров
    switch (@$_GET ['act']) {
        case 'file_import' : {
                $form = new form('?' . passgen());
                $form->text('url', __('URL'), 'http://');
                $form->button(__('Импортировать'), 'file_import');
                $form->display();
            }
            break;
        case 'write_dir' : {
                $form = new form('?' . passgen());
                $form->text('name', __('Название папки'));
                $form->block('<div class="ui mini info message">' . __('На сервере создастся папка на транслите') . '</div>');
                $form->button(__('Создать'), 'write_dir');
                $form->display();
            }
            break;

        case 'edit_unlink' : {
                if ($rel_path) {
                    $form = new form('?' . passgen());
                    $form->block('<div class="ui mini yellow message">' . __('Все данные, находящиеся в этой папке будут безвозвратно удалены') . '</div>');
                    $form->button(__('Удалить'), 'edit_unlink');
                    $form->display();
                }
            }
            break;
        case 'edit_path' : {
                // перемещение папки
                $options = array();
                // список папок в загруз-центре
                $root_dir = new files(FILES . '/.downloads');
                $dirs = $root_dir->getPathesRecurse($dir);
                foreach ($dirs as $dir2) {

                    if ($dir2->group_show > $user->group || $dir2->group_write > $user->group) {
                        // если нет прав на чтение папки или на запись в папку, то пропускаем
                        continue;
                    }

                    if ($dir2->path_rel == $dir->path_rel) {
                        $options [] = array($dir2->path_rel, $dir2->getPathRu(), true);
                    } else {
                        $options [] = array($dir2->getPath(), text::toValue($dir2->getPathRu() . ' <- ' . $dir->runame));
                    }
                }

                // список папок обменника
                $root_dir = new files(FILES . '/.obmen');
                $dirs = $root_dir->getPathesRecurse($dir);
                foreach ($dirs as $dir2) {

                    if ($dir2->group_show > $user->group || $dir2->group_write > $user->group) {
                        // если нет прав на чтение папки или на запись в папку, то пропускаем
                        continue;
                    }

                    if ($dir2->path_rel == $dir->path_rel) {
                        $options [] = array($dir2->path_rel, $dir2->getPathRu(), true);
                    } else {
                        $options [] = array($dir2->getPath(), text::toValue($dir2->getPathRu() . ' <- ' . $dir->runame));
                    }
                }

                $form = new form('?' . passgen());
                $form->select('path_rel_new', __('Новый путь'), $options);
                $form->button(__('Сохранить'), 'edit_path');
                $form->display();
            }
            break;
        case 'edit_prop' : {
                $groups = groups::load_ini(); // загружаем массив групп

                $form = new form('?' . passgen());
                $form->text('name', __('Название папки'), $dir->runame);
                if ($rel_path && $dir->name{0} !== '.') {
                    $form->block('<div class="ui mini info message">' . __('На сервере папка будет на транслите') . '</div>');
                } else {
                    $form->block('<div class="ui mini info message">' . __('Изменится только отображаемое название') . '</div>');
                }

                $form->textarea('description', __('Описание'), $dir->description);

                if ($rel_path) {
                    $form->text('position', __('Позиция'), $dir->position);
                    $form->block('<div class="ui mini info message">' . __('Если у папок одинаковая позиция, то они сортируются по имени') . '</div>');
                }

                $order_keys = $dir->getKeys();
                $options = array();
                foreach ($order_keys as $key => $key_name) {
                    $options [] = array($key, $key_name, $key == $dir->sort_default);
                }
                $form->select('sort_default', __('Сортировка по-умолчанию'), $options, false);

                $options = array();
                foreach ($groups as $type => $value) {
                    $options [] = array($type, $value ['name'], $type == $dir->group_show);
                }
                $form->select('group_show', __('Просмотр папки'), $options, false);
                $form->block('<div class="ui mini info message">' . __('При большом кол-ве вложенных объектов изменение данного параметра может затянуться (и подвесить сервер)') . '</div>');

                $options = array();
                foreach ($groups as $type => $value) {
                    $options [] = array($type, $value ['name'], $type == $dir->group_write, false);
                }
                $form->select('group_write', __('Выгрузка файлов'), $options, false);

                $options = array();
                foreach ($groups as $type => $value) {
                    $options [] = array($type, $value ['name'], $type == $dir->group_edit);
                }
                $form->select('group_edit', __('Изменение параметров и создание папок'), $options, false);

                $form->textarea('meta_description', __('Описание') . ' [META]', $dir->meta_description);
                $form->textarea('meta_keywords', __('Ключевые слова (через запятую)') . ' [META]', $dir->meta_keywords);
                $form->button(__('Сохранить'), 'edit_prop');
                $form->display();
            }
            break;
    }

    $doc->opt(__('Импорт'), '?act=file_import', false, '<i class="fa fa-download fa-fw"></i>');
    $doc->opt(__('Создать папку'), '?order=' . $order . '&amp;act=write_dir', false, '<i class="fa fa-plus fa-fw"></i>');
    $doc->opt(__('Параметры'), '?order=' . $order . '&amp;act=edit_prop', false, '<i class="fa fa-cog fa-fw"></i>');

    if ($rel_path && $dir->name{0} !== '.') {
        $doc->opt(__('Перемещение'), '?order=' . $order . '&amp;act=edit_path', false, '<i class="fa fa-exchange fa-fw"></i>');
        $doc->opt(__('Удаление'), '?order=' . $order . '&amp;act=edit_unlink', false, '<i class="fa fa-trash-o fa-fw"></i>');
    }
}