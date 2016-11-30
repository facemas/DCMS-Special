<?php

include_once '../sys/inc/start.php';
$doc = new document(1); // инициализация документа для браузера
$doc->title = __('Мой аватар');

$avatar_file_name = $user->id . '.jpg';
$avatars_path = FILES . '/.avatars'; // папка с аватарами
$avatars_dir = new files($avatars_path);

if (!empty($_FILES ['file'])) {
    if ($_FILES ['file'] ['error']) {
        $doc->err(__('Ошибка при загрузке'));
    } elseif (!$_FILES ['file'] ['size']) {
        $doc->err(__('Содержимое файла пусто'));
    } elseif (!preg_match('#\.jpe?g$#ui', $_FILES ['file'] ['name'])) {
        $doc->err(__('Неверное расширение файла'));
    } elseif (!$img = @imagecreatefromjpeg($_FILES ['file'] ['tmp_name'])) {
        $doc->err(__('Файл не является изображением JPEG'));
    } elseif (@imagesx($img) < 128) {
        $doc->err(__('Ширина изображения должна быть не менее 128 px'));
    } elseif (@imagesy($img) < 128) {
        $doc->err(__('Высота изображения должна быть не менее 128 px'));
    } else {
        if ($avatars_dir->is_file($avatar_file_name)) {
            $avatar = new files_file($avatars_path, $avatar_file_name);
            $avatar->delete(); // удаляем старый аватар
        }

        if ($files_ok = $avatars_dir->filesAdd(array($_FILES ['file'] ['tmp_name'] => $avatar_file_name))) {
            $avatars_dir->group_show = 0;
            $files_ok [$_FILES ['file'] ['tmp_name']]->group_show = 0;
            $files_ok [$_FILES ['file'] ['tmp_name']]->id_user = $user->id;
            $files_ok [$_FILES ['file'] ['tmp_name']]->group_edit = max($user->group, 2);

            unset($files_ok);

            $q = $db->prepare("DELETE FROM `avatar_komm` WHERE `id_avatar` = ?");
            $q->execute(Array($user->id));
            $q = $db->prepare("DELETE FROM `avatar_like` WHERE `id_avatar` = ?");
            $q->execute(Array($user->id));
            $user->avatar = 1;

            $doc->msg(__('Аватар успешно установлен'));
        } else {
            $doc->err(__('Не удалось сохранить выгруженный файл'));
        }
    }
}

// Аватар 
if ($path = $user->getAvatar($doc->img_max_width())) {

    if (!empty($_POST ['delete'])) {
        $avatar = new files_file($avatars_path, $avatar_file_name);
        if ($avatar->delete()) {
            $doc->msg(__('Аватар успешно удален'));
            $user->avatar = 0;
            $doc->ret(__('Мой аватар'), '?' . passgen());
            header('Refresh: 1; url=?' . passgen());
            exit;
        } else {
            $doc->err(__('Не удалось удалить аватар'));
        }
    }

    echo "<div class='listing post' style='padding: 5px;'>";
    echo "<img class='ui image' src='" . $path . "' alt='" . __('Мой аватар') . "' /><br />\n";
    echo "</div>";

    $form = new form('?' . passgen());
    $form->block('<input type="submit" name="delete" value="' . __('Удалить текущий') . '" class="tiny ui grey button" />');
    $form->display();
}

$form = new form('?' . passgen());
$form->file('file', __('Файл аватара') . ' (*.jpg)');
$form->button(__('Выгрузить'));
$form->display();
