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

if ($ank->id == $user->id) {
    $doc->title = __('Мои фотоальбомы');
} else {
    $doc->title = __('Фотоальбомы %s', $ank->login);
}


$doc->description = __('Фотоальбомы %s', $ank->login);
$doc->keywords[] = $ank->login;


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

// создание альбома
if ($ank->id == $user->id && !empty($_GET ['act']) && $_GET ['act'] == 'create') {
    $doc->title .= ' - ' . __('Создать альбом');

    if (!empty($_POST ['name'])) {
        $name = text::for_name($_POST ['name']);

        if (!$name) {
            $doc->err(__('Название состоит из запрещенных символов'));
        } elseif (!$album = $albums_dir->mkdir($name)) {
            $doc->err(__('Не удалось создать альбом'));
        } else {
            $doc->ret(__('К альбому %s', $name), 'photos.php?id=' . $ank->id . '&mp;album=' . urlencode($album->name));
            $doc->ret(__('К альбомам'), '?id=' . $ank->id);
            header('Refresh: 1; url=photos.php?id=' . $ank->id . '&album=' . urlencode($album->name));
            $doc->msg(__('Альбом "%s" успешно создан', $name));
            exit();
        }
    }

    $form = new form(new url(null, array('act' => 'create')));
    $form->text('name', __('Название альбома'));
    $form->button(__('Создать'));
    $form->display();

    $doc->ret(__('К альбомам'), '?id=' . $ank->id . '&amp;' . passgen());
    exit();
}

$content = $albums_dir->getList('time_add:desc');
$dirs = &$content ['dirs'];

$listing = new ui_components();
$listing->ui_segment = true; //подключаем css segments
$listing->class = 'ui segments';

for ($i = 0; $i < count($dirs); $i++) {
    $post = $listing->post();
    $post->class = 'ui segment';
    $post->list = true;
    $post->icon($dirs[$i]->icon());
    $post->title = text::toValue($dirs [$i]->runame);
    $post->url = "photos.php?id={$ank->id}&amp;album=" . urlencode($dirs [$i]->name);
}
$listing->display(__('Фотоальбомы отсутствуют'));



if ($user->id == $ank->id) {
    $doc->opt(__('Создать альбом'), '?id=' . $ank->id . '&amp;act=create', false, '<i class="fa fa-plus fa-fw"></i>');
}