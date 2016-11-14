<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Файлы');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора сообщения'));
    exit;
}
$id_message = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `forum_messages` WHERE `id` = ?");
$q->execute(Array($id_message));

if (!$message = $q->fetch()) {
    $doc->toReturn();
    $doc->err(__('Сообщение не найдено'));
    exit;
}


$q = $db->prepare("SELECT * FROM `forum_themes` WHERE `id` = ?");
$q->execute(Array($message['id_theme']));

if (!$theme = $q->fetch()) {
    $doc->toReturn();
    $doc->err(__('Тема не найдена'));
    exit;
}


$autor = new user((int) $message['id_user']);

$access_edit = false;
$edit_time = $message['time'] - TIME + 600;

if ($user->group >= $message['group_edit'])
    $access_edit = true;
elseif ($user->id == $autor->id && $edit_time > 0) {
    $access_edit = true;
    $doc->msg(__('Для выгрузки файлов осталось %s сек', $edit_time));
}

if (!$access_edit) {
    $doc->toReturn();
    $doc->err(__('Сообщение не доступно для редактирования'));
    exit;
}


$forum_dir = new files(FILES . '/.forum');

$theme_dir_path = FILES . '/.forum/' . $message['id_theme'];
if (!@is_dir($theme_dir_path)) {
    if (!$th_dir = $forum_dir->mkdir(__('Файлы темы #%d', $message['id_theme']), $message['id_theme']))
        $doc->access_denied(__('Не удалось создать папку под файлы темы'));

    $th_dir->group_show = $theme['group_show'];
    $th_dir->group_write = max($theme['group_write'], 2);
    $th_dir->group_edit = $theme['group_edit'];
    unset($th_dir);
}

$theme_dir = new files($theme_dir_path);

$post_dir_path = FILES . '/.forum/' . $message['id_theme'] . '/' . $message['id'];

if (!@is_dir($post_dir_path)) {
    if (!$p_dir = $theme_dir->mkdir(__('Файлы к сообщению #%d', $message['id']), $message['id'])) {
        $doc->access_denied(__('Не удалось создать папку под файлы сообщения'));
    }
    $p_dir->id_user = $user->id;
    $p_dir->group_show = 0; // папка будет доступна гостям
    unset($p_dir);
}

$dir = new files($post_dir_path);


if (!empty($_FILES['file'])) {
    if ($_FILES['file']['error']) {
        $doc->err(__('Ошибка при загрузке'));
    } elseif (!$_FILES['file']['size']) {
        $doc->err(__('Содержимое файла пусто'));
    } elseif ($dcms->forum_files_upload_size && $_FILES['file']['size'] > $dcms->forum_files_upload_size) {
        $doc->err(__('Размер файла превышает установленные ограниченияя'));
    } else {
        if ($files_ok = $dir->filesAdd(array($_FILES['file']['tmp_name'] => $_FILES['file']['name']))) {
            $files_ok[$_FILES['file']['tmp_name']]->id_user = $user->id;
            $files_ok[$_FILES['file']['tmp_name']]->group_show = $dir->group_show;
            $files_ok[$_FILES['file']['tmp_name']]->group_edit = max($user->group, $dir->group_write, 2);
            unset($files_ok);
            $doc->msg(__('Файл "%s" успешно добавлен', $_FILES['file']['name']));
        } else {
            $doc->err(__('Не удалось сохранить выгруженный файл'));
        }
    }
} elseif (!empty($_GET['delete'])) {
    
}

$doc->title = __('Файлы к сообщению от "%s"', $autor->login);

$listing = new listing();
$content = $dir->getList('time_add:asc');

foreach ($content['files'] AS $file) {
    $post = $listing->post();
    $post->icon($file->icon());
    $post->image = $file->image();
    $post->title = text::toValue($file->runame);
    $post->url = "/files{$dir->path_rel}/" . urlencode($file->name) . ".htm";
    $post->content[] = $file->properties;
}
$listing->display(__('Вложения отсутствуют'));

$form = new form(new url());
$form->file('file', __("Файл"));
$form->bbcode('* ' . __('Файлы, размер которых превышает %s, загружены не будут', misc::getDataCapacity($dcms->forum_files_upload_size)));
$form->button(__('Прикрепить'));
$form->display();

$doc->ret(__('В тему'), 'theme.php?id=' . $message['id_theme']);
