<?php

$doc->title = __('Файл %s - скриншоты', $file->runame);

if (!empty($_FILES['file'])) {
    if ($_FILES['file']['error']) {
        $doc->err(__('Ошибка при загрузке'));
    } elseif (!$_FILES['file']['size']) {
        $doc->err(__('Содержимое файла пусто'));
    } else {
        $info = pathinfo($_FILES['file']['name']);

        switch (strtolower($info['extension'])) {
            case 'jpg':
                $img_screen = @imagecreatefromjpeg($_FILES['file']['tmp_name']);
                break;
            case 'jpeg':
                $img_screen = @imagecreatefromjpeg($_FILES['file']['tmp_name']);
                break;
            case 'gif':
                $img_screen = @imagecreatefromgif($_FILES['file']['tmp_name']);
                break;
            case 'png':
                $img_screen = @imagecreatefrompng($_FILES['file']['tmp_name']);
                break;
            default:
                $doc->err(__('Расширение файла не опознано'));
                break;
        }

        if (!empty($img_screen)) {
            if ($file->screenAdd($img_screen)) {
                header('Refresh: 1; url=?order=' . $order . '&act=edit_screens&' . SID);
                $doc->ret('Вернуться', '?order=' . $order . '&amp;act=edit_screens');

                $doc->msg(__('Скриншот успешно добавлен'));
                exit;
            } else {
                $doc->err(__('Ошибка при добавлении скриншота'));
            }
        }
    }
}

if (isset($_GET['delete'])) {
    if ($file->screenDelete($_GET['delete'])) {
        header('Refresh: 1; url=?order=' . $order . '&act=edit_screens&' . SID);
        $doc->ret(__('Вернуться'), '?order=' . $order . '&amp;act=edit_screens');

        $doc->msg(__('Скриншот успешно удален'));
        exit;
    } else {
        $doc->err(__('Ошибка при удалении скриншота'));
    }
}

$screens_count = $file->getScreensCount();

$listing = new listing();
for ($i = 0; $i < $screens_count; $i++) {
    $post = $listing->post();
    $post->image = $file->getScreen(48, $i);
    $post->title = __('Скриншот №%s', ($i + 1));
    $post->action('delete', '?order=' . $order . '&amp;act=edit_screens&amp;delete=' . $i);
}
$listing->display(__('Скриншоты отсутствуют'));

$form = new form(new url());
$form->file('file', __('Скриншот'));
$form->button(__('Добавить'));
$form->display();
$doc->ret(__('К описанию'), '?order=' . $order);
exit;
