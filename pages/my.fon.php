<?php

include_once '../sys/inc/start.php';

if (AJAX) {
    $doc = new document_json(1);
} else {
    $doc = new document(1);
}

$doc->title = __('Фон профиля');

$fon = new user_fon($user->id);
$avatar_file_name = $user->id . '.jpg';
$avatars_path = FILES . '/.fon'; // папка с фоном
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
    } elseif (@imagesx($img) < 400) {
        $doc->err(__('Ширина изображения должна быть не менее 128 px'));
    } elseif (@imagesy($img) < 250) {
        $doc->err(__('Высота изображения должна быть не менее 128 px'));
    } else {
        if ($avatars_dir->is_file($avatar_file_name)) {
            $avatar = new files_file($avatars_path, $avatar_file_name);
            $avatar->delete(); // удаляем старый фон
        }

        if ($files_ok = $avatars_dir->filesAdd(array($_FILES ['file'] ['tmp_name'] => $avatar_file_name))) {
            $avatars_dir->group_show = 0;
            $files_ok [$_FILES ['file'] ['tmp_name']]->group_show = 0;
            $files_ok [$_FILES ['file'] ['tmp_name']]->id_user = $user->id;
            $files_ok [$_FILES ['file'] ['tmp_name']]->group_edit = max($user->group, 2);

            unset($files_ok);
            $doc->msg(__('Фон успешно установлен'));
        } else {
            $doc->err(__('Не удалось сохранить выгруженный файл'));
        }
    }
}

if ($path = $fon->getScreen($doc->img_max_width())) {

    if (!empty($_POST ['delete'])) {
        $avatar = new files_file($avatars_path, $avatar_file_name);

        if ($avatar->delete()) {
            $doc->msg(__('Фон успешно удален'));

            $doc->ret(__('Мой фон профиля'), '?' . passgen());
            header('Refresh: 1; url=?' . passgen());
            exit;
        } else {
            $doc->err(__('Не удалось удалить фон'));
        }
    }

    echo "<div class='listing post' style='padding: 5px;'>";
    echo "<img class='ui image' src='" . $path . "' alt='" . __('Фон профиля') . "' /><br />\n";
    echo "</div>";

    $form = new form('?' . passgen());
    $form->block('<input type="submit" name="delete" value="' . __('Удалить текущий фон') . '" class="tiny ui grey button" />');
    $form->display();
}

$form = new form('?' . passgen());
$form->file('file', __('Файл фона') . ' (*.jpg)');
$form->button(__('Обновить фон'));
$form->display();
