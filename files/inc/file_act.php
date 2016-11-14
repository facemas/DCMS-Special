<?php

if (isset($_POST['edit_unlink']) && $file->name{0} !== '.') {
    $id_user = $file->id_user;
    $runame = $file->runame;
    if ($file->delete()) {
        if ($id_user && $id_user != $user->id && !empty($_POST['reason'])) {
            $reason = text::input_text(@$_POST['reason']);
            $ank = new user($id_user);
            $ank->mess("Ваш файл $runame был удален.\nПричина: $reason.\nУдалил [user]$user->id[/user].");

            $dcms->log('Файлы', 'Удаление файла ' . $runame . ' пользователя [user]' . $id_user . '[/user]. Причина: ' . $reason);
        } else {
            $dcms->log('Файлы', 'Удаление файла ' . $runame . ' пользователя [user]' . $id_user . '[/user]');
        }

        $doc->msg(__('Файл успешно удален'));
        $doc->ret(__('Вернуться'), './?' . passgen() . '&amp;order=' . $order);
        header('Refresh: 1; url=./?' . passgen() . '&order=' . $order);
        exit;
    } else {
        $doc->err(__('Ошибка при удалении файла'));
    }
}

if (isset($_POST['edit_prop'])) {
    $groups = groups::load_ini(); // загружаем массив групп

    if (isset($_POST['description']))
        $file->description = text::input_text($_POST['description']);
    if (isset($_POST['description_small']))
        $file->description_small = text::input_text($_POST['description_small']);
    if (isset($_POST ['meta_description']))
        $file->meta_description = text::input_text($_POST ['meta_description']);
    if (isset($_POST ['meta_keywords']))
        $file->meta_keywords = text::input_text($_POST ['meta_keywords']);

    if (!empty($_POST['name'])) {
        $runame = text::for_name($_POST['name']);
        $name = basename(text::for_filename($runame), '.' . $file->ext);

        if ($file->ext) {
            $new_filename = $name . '.' . $file->ext;
        } else {
            $new_filename = $name;
        }

        if (!$access_write_dir)
            $new_filename = false;

        if ($runame != $file->runame) {
            if (!$runame || !$name)
                $doc->err(__('Неверно задано имя файла'));
            elseif (!$file->rename($runame, $new_filename))
                $doc->err(__('Не удалось переименовать файл'));
            else {

                $dcms->log('Файлы', 'Изменение имени файла ' . $file->runame . ' на ' . $runame);
                $doc->msg(__('Новое название файла: "%s"', $runame));
                $doc->ret(__('Вернуться'), './' . $file->name . '.htm?order=' . $order);
                header('Refresh: 1; url=./' . $file->name . '.htm?order=' . $order);
                exit;
            }
        }
    }
    if ($file->group_edit <= $user->group) {
        if (isset($_POST['group_show'])) { // просмотр
            $group_show = (int)$_POST['group_show'];
            if (isset($groups[$group_show]) && $group_show != $file->group_show) {
                $file->group_show = $group_show;
                $doc->msg(__('Просмотр файла разрешен группе "%s" и выше', groups::name($group_show)));
                $dcms->log('Файлы', 'Изменение привилегий просмотра файла [url="/files' . $file->getPath() . '"]' . $file->runame . '[/url] на ' . groups::name($group_show));
            }
        }

        if (isset($_POST['group_edit'])) { // редактирование
            $group_edit = (int)$_POST['group_edit'];
            if (isset($groups[$group_edit]) && $group_edit != $file->group_edit) {
                if ($file->group_show > $group_show)
                    $doc->err(__('Для изменения параметров файла группе "%s" сначала необходимо дать права на просмотр файла', groups::name($group_edit)));
                else {
                    $file->group_edit = $group_edit;
                    $doc->msg(__('Изменять параметры файла теперь разрешено группе "%s" и выше', groups::name($group_edit)));
                    $dcms->log('Файлы', 'Изменение привилегий редактирования файла [url="/files' . $file->getPath() . '"]' . $file->runame . '[/url] на ' . groups::name($group_edit));
                }
            }
        }
    }
}


if (isset($_POST ['edit_path']) && !empty($_POST ['path_rel_new'])) {

    $dir_new = new files(FILES . $_POST ['path_rel_new']);

    if (strpos($dir_new->path_abs, FILES) !== 0 || !file_exists($dir_new->path_abs)) {
        $doc->err(__('Перемещать файлы разрешено только в пределах загруз-центра'));
        exit;
    }


    $path_abs_new = $dir_new->path_abs;

    if (!empty($path_abs_new)) {
        if ($file->moveTo($path_abs_new)) {
            // записываем свое действие в общий лог
            $dcms->log('Файлы', 'Перемещение файла [url="/files' . $file->getPath() . '.htm"]' . $file->runame . '[/url]');

            $doc->msg(__('Файл успешно перемещен'));
            $doc->ret(__('Вернуться'), '/files' . $file->getPath() . '.htm?' . passgen());
            header('Refresh: 1; url=/files' . $file->getPath() . '.htm?' . passgen());
            exit();
        } else {
            $doc->err(__('При перемещении файла возникла ошибка'));
        }
    } else {
        $doc->err(__('Ошибка при выборе нового каталога'));
    }
}