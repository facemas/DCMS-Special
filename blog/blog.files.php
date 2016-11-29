<?php

include_once '../sys/inc/start.php';

$doc = new document(1);

$doc->title = __('Файлы');

if (!isset($_GET ['id']) || !is_numeric($_GET ['id'])) {
    if (isset($_GET ['return'])) {
        header('Refresh: 1; url=' . $_GET ['return']);
    } else {
        header('Refresh: 1; url=./');
    }

    $doc->err(__('Запись не выбрана'));
    exit();
}

$id_blog = (int) $_GET ['id'];

$q = $db->prepare("SELECT * FROM `blog` WHERE `id` = ?");
$q->execute(Array($id_blog));

if (!$blog = $q->fetch()) {
    if (isset($_GET ['return'])) {
        header('Refresh: 1; url=' . $_GET ['return']);
    } else {
        header('Refresh: 1; url=./');
    }

    $doc->err(__('Запись не существует'));
    exit;
}

$b_file = $db->query("SELECT * FROM `blog_cfg` WHERE `id`= '1' ")->fetch();
$autor = new user((int) $blog['autor']);
if ($autor->id == $user->id || $user->group >= 2) {

    $blog_dir = new files(FILES . '/.blog');
    $blog_dir_path = FILES . '/.blog/' . $id_blog;
    if (!@is_dir($blog_dir_path)) {
        if (!$th_dir = $blog_dir->mkdir(__('Файлы записи #%d', $id_blog), $id_blog)) {
            $doc->access_denied(__('Не удалось создать папку под файлы .blog'));
        }
        $th_dir->group_show = 0;
        $th_dir->group_write = max(1, 2);
        $th_dir->group_edit = 4;
        unset($th_dir);
    }
    $dir = new files($blog_dir_path);
    if (!empty($_FILES['file'])) {
        if ($_FILES['file']['error']) {
            $doc->err(__('Ошибка при загрузке'));
        } elseif (!$_FILES['file']['size']) {
            $doc->err(__('Содержимое файла пусто'));
        } elseif ($b_file['file'] && $_FILES['file']['size'] > $b_file['file']) {
            $doc->err(__('Размер файла превышает установленные ограниченияя'));
        } else {
            if ($files_ok = $dir->filesAdd(array($_FILES['file']['tmp_name'] => $_FILES['file']['name']))) {
                $files_ok[$_FILES['file']['tmp_name']]->id_user = $user->id;
                $files_ok[$_FILES['file']['tmp_name']]->group_show = $dir->group_show;
                $files_ok[$_FILES['file']['tmp_name']]->group_edit = 4;
                unset($files_ok);
                $doc->msg(__('Файл "%s" успешно добавлен', $_FILES['file']['name']));
            } else {
                $doc->err(__('Не удалось сохранить выгруженный файл'));
            }
        }
    }

    if (isset($_GET['delete'])) {
        $name = text::input_text($_GET['name']);
        if ($dir->is_file($name)) {
            $file = new files_file($blog_dir_path, $name);
            if ($file->delete()) {
                $doc->msg(__('Файл %s успешно удален', $name));
                header('Refresh: 1; url=/blog/blog.files.php?id=' . $blog['id']);
            }
        }
        exit();
    }
    $doc->title = __('Файлы к записи');
    $listing = new listing();
    $content = $dir->getList('time_add:asc');
    foreach ($content['files'] AS $file) {
        $post = $listing->post();
        $post->icon($file->icon());
        $post->image = $file->image();
        $post->title = $file->runame;
        $post->url = "/files{$dir->path_rel}/" . urlencode($file->name) . ".htm";
        $post->action('trash-o', "?id=" . $blog['id'] . "&amp;delete&amp;name=" . urlencode($file->name));
        $post->content[] = $file->properties;
    }
    $listing->display(__('Файлы отсутствуют'));

    $form = new design();
    $form->assign('method', 'post');
    $form->assign('files', 1);
    $form->assign('action', "/blog/blog.files.php?id=$blog[id]&amp;" . passgen() . (isset($_GET['return']) ? '&amp;return=' . urlencode($_GET['return']) : null));
    $elements = array();
    $elements[] = array('type' => 'file', 'title' => 'Файл', 'br' => 0, 'info' => array('name' => 'file'));
    $elements[] = array('type' => 'submit', 'br' => 0, 'info' => array('value' => __('Прикрепить'), 'class' => 'tiny ui blue button')); // кнопка
    $form->assign('el', $elements);
    $form->display('input.form.tpl');
} else {
    $doc->err(__('Доступ ограничен'));
}

$doc->ret(__('К записи'), 'blog.php?blog=' . $blog['id']);
