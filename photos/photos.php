<?php

include_once '../sys/inc/start.php';
$doc = new document ();
$doc->title = __('Фотоальбомы');


if (!empty($_GET ['id'])) {
    $ank = new user((int) $_GET ['id']);
} else {
    $ank = $user;
}

if (!$ank->group) {
    $doc->access_denied(__('Ошибка пользователя'));
}

// папка фотоальбомов пользователей
$photos = new files(FILES . '/.photos');
// папка альбомов пользователя
$albums_path = FILES . '/.photos/' . $ank->id;

if (!@is_dir($albums_path)) {
    if (!$albums_dir = $photos->mkdir($ank->login, $ank->id)) {
        $doc->access_denied(__('Не удалось создать папку под фотоальбомы пользователя'));
    }
    $albums_dir->group_show = 0;
    $albums_dir->group_write = max($ank->group, 2);
    $albums_dir->group_edit = max($ank->group, 4);
    $albums_dir->id_user = $ank->id;
    unset($albums_dir);
}

$albums_dir = new files($albums_path);

if (empty($_GET ['album']) || !$albums_dir->is_dir($_GET ['album'])) {
    $doc->err(__('Запрошеный альбом не существует'));
    $doc->ret(__('К альбомам %s', $name), 'albums.php?id=' . $ank->id);
    header('Refresh: 1; url=albums.php?id=' . $ank->id);
    exit();
}

$album_name = (string) $_GET ['album'];
$album = new files($albums_path . '/' . $album_name);
$doc->title = $album->runame;

$doc->description = __('Фотоальбом пользователя %s:%s', $ank->login, $album->runame);
$doc->keywords [] = $album->runame;
$doc->keywords [] = $ank->login;

if (!empty($_GET ['act']) && $ank->id == $user->id) {
    switch ($_GET ['act']) {
        case 'prop' :
            $doc->title .= ' - ' . __('Параметры');

            if (!empty($_POST ['prop'])) {
                if ($album->id_user = $user->id) {

                    if (isset($_POST['group_show'])) {
                        $album->group_show = $_POST['group_show'];
                    }

                    $album->runame = text::input_text(@$_POST['name']);
                    $album->description = text::input_text(@$_POST['description']);
                    $doc->msg(__('Изменения сохранены'));
                    $doc->ret(__('Альбомы %s', $ank->login), 'albums.php?id=' . $ank->id);
                } else {
                    $doc->err(__('Не удалось сменит название альбома'));
                    $doc->ret(__('В альбом'), '?id=' . $ank->id . '&amp;album=' . urlencode($album->name));
                    $doc->ret(__('Альбомы %s', $ank->login), 'albums.php?id=' . $ank->id);
                }
            }

            $form = new form(new url(null, array('act' => 'prop')));
            $form->text('name', 'Название', $album->runame);
            $form->textarea('description', 'Описание', $album->description);
            $options = array();
            $options [] = array('0', __('Все'), $album->group_show == "0");
            $options [] = array('1', __('Зарегистрированные'), $album->group_show == "1");
            $form->select('group_show', __('Кто видит альбом'), $options);
            $form->block('<input type="submit" name="prop" value="' . __('Сохранить') . '" class="tiny ui blue button" />');
            $form->display();

            $doc->ret(__('В альбом'), '?id=' . $ank->id . '&amp;album=' . urlencode($album->name));
            exit();
        case 'photo_add' :
            $doc->title .= ' - ' . __('Выгрузка фото');

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
                    if ($files_ok = $album->filesAdd(array($_FILES ['file'] ['tmp_name'] => $_FILES ['file'] ['name']))) {
                        $files_ok [$_FILES ['file'] ['tmp_name']]->id_user = $ank->id;
                        $files_ok [$_FILES ['file'] ['tmp_name']]->group_edit = max($ank->group, $album->group_write, 2);

                        unset($files_ok);
                        $doc->msg(__('Фотография "%s" успешно добавлена', $_FILES ['file'] ['name']));
                    } else {
                        $doc->err(__('Не удалось сохранить выгруженный файл'));
                    }
                }
            }

            $form = new form(new url(null, array('act' => 'photo_add')));
            $form->file('file', __('Фотография') . ' (*.jpg)');
            $form->button(__('Выгрузить'));
            $form->display();

            $doc->ret(__('В альбом'), '?id=' . $ank->id . '&amp;album=' . urlencode($album->name));
            exit();
        case 'delete' :
            $doc->title .= ' - ' . __('Удаление');

            if (!empty($_POST ['delete'])) {

                if ($album->delete()) {
                    $doc->msg(__('Альбом успешно удален'));
                    $doc->ret(__('Альбомы %s', $ank->login), 'albums.php?id=' . $ank->id);
                    header('Refresh: 1; url=albums.php?id=' . $ank->id . '&' . passgen());
                } else {

                    $doc->err(__('Не удалось удалить альбом'));
                    $doc->ret(__('В альбом'), '?id=' . $ank->id . '&amp;album=' . urlencode($album->name));
                    $doc->ret(__('Альбомы %s', $ank->login), 'albums.php?id=' . $ank->id);
                    header('Refresh: 1; url=?id=' . $ank->id . '&album=' . urlencode($album->name) . '&' . passgen());
                }
                exit();
            }

            $form = new form(new url(null, array('act' => 'delete')));
            $form->block('<div class="ui mini yellow message">' . __('Все данные, относящиеся к данному альбому будут безвозвратно удалены.') . '</div>');
            $form->block('<input type="submit" name="delete" value="' . __('Удалить альбом') . '" class="tiny ui blue button" />');

            $form->display();

            $doc->ret(__('В альбом'), '?id=' . $ank->id . '&amp;album=' . urlencode($album->name));
            exit();
    } // switch
}

$list = $album->getList('time_add:desc'); // получение содержимого папки альбома
$files = $list ['files']; // получение только файлов

$pages = new pages(count($files));
$start = $pages->my_start();
$end = $pages->end();

if ($ank->id == $user->id) {
    $listing = new ui_components();
    $listing->ui_menu = true;

    $post = $listing->post();
    $post->head = '<div class="ui icon menu">
        <span data-tooltip="' . __('Выгрузить фото') . '" data-position="bottom left">
        <a class="item" href="?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;act=photo_add"><i class="fa fa-camera fa-fw"></i></a>
            </span>
            <span data-tooltip="' . __('Параметры альбома') . '" data-position="bottom left">
        <a class="item" href="?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;act=prop"><i class="fa fa-cog fa-fw"></i></a>
            </span>
            <span data-tooltip="' . __('Удалить альбом') . '" data-position="bottom left">
        <a class="item" href="?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;act=delete"><i class="fa fa-trash-o fa-fw"></i></a>
            </span>
        </div>';

    $listing->display();
}

/*
  if ($ank->id == $user->id) {
  $doc->opt(__('Выгрузить фото'), '?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;act=photo_add');
  $doc->opt(__('Параметры'), '?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;act=prop');
  $doc->opt(__('Удалить альбом'), '?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;act=delete');
  }
 * 
 */

$listing = new ui_components();
$listing->ui_segment = true; //подключаем css segments
$listing->ui_image = true; //подключаем css segments

$display = false;

echo "<div class='ui segment'>";
echo "<div class='ui tiny images'>";
for ($i = $start; $i < $end && $i < $pages->posts; $i++) {
    $display = true;

    echo "<a href='photo.php?id=$ank->id&amp;album=" . urlencode($album->name) . "&amp;photo=" . urlencode($files [$i]->name) . "'>";
    echo "<img class='ui image' src='" . $files[$i]->image() . "' />";
    //$post->title = text::toValue($files[$i]->runame);

    if ($comments = $files [$i]->comments) {
        //$post->content[] = __('%s комментари' . misc::number($comments, 'й', 'я', 'ев'), $comments);
    }

    if ($properties = $files [$i]->properties) {
        // Параметры файла (только основное)
        //$post->content[] = $properties;
    }
    echo "</a>";
}

echo "</div>";
if ($display) {
    $listing->post();
}
$listing->display(__('Фотографии отсутствуют'));
echo "</div>";

$pages->display('?id=' . $ank->id . '&amp;album=' . urlencode($album->name) . '&amp;'); // вывод страниц

$doc->ret(__('Альбомы %s', $ank->login), 'albums.php?id=' . $ank->id);
