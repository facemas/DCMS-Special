<?php

defined('SOCCMS') or die;
$pathinfo = pathinfo($abs_path);
$dir = new files($pathinfo['dirname']);

if ($dir->group_show > $user->group) {
    $doc->access_denied(__('У Вас нет прав для просмотра файлов в данной папке'));
}
$access_write_dir = $dir->group_write <= $user->group || ($dir->id_user && $user->id == $dir->id_user);

$order_keys = $dir->getKeys();
if (!empty($_GET['order']) && isset($order_keys[$_GET['order']])) {
    $order = $_GET['order'];
} else {
    $order = 'runame:asc';
}

$file = new files_file($pathinfo['dirname'], $pathinfo['basename']);

if ($file->group_show > $user->group) {
    $doc->access_denied(__('У Вас нет прав для просмотра данного файла'));
}

$access_edit = $file->group_edit <= $user->group || ($file->id_user && $file->id_user == $user->id);

if ($access_edit && isset($_GET['act']) && $_GET['act'] == 'edit_screens') {
    include 'inc/screens_edit.php';
}


$doc->title = __('Файл %s - скачать', $file->runame);
$doc->description = $file->meta_description ? $file->meta_description : $dir->meta_description;
$doc->keywords = $file->meta_keywords ? explode(',', $file->meta_keywords) : ($dir->meta_keywords ? explode(',', $dir->meta_keywords) : '');

if ($access_edit) {
    include 'inc/file_act.php';
}

if ($user->group && $file->id_user != $user->id && isset($_POST['rating'])) {
    $my_rating = (int) $_POST['rating'];
    if (isset($file->ratings[$my_rating])) {
        $file->rating_my($my_rating);
        $doc->msg(__('Ваша оценка файла успешно принята'));

        header('Refresh: 1; url=?order=' . $order . '&' . passgen() . SID);
        $doc->act(__('Вернуться'), '?order=' . $order . '&amp;' . passgen());
        exit;
    } else {
        $doc->err(__('Нет такой оценки файла'));
    }
}

if ($access_edit) {
    $listing = new ui_components();
    $listing->ui_menu = true;

    $post = $listing->post();
    $post->head = '<div class="ui icon menu">
        <span data-tooltip="' . __('Скриншоты') . '" data-position="bottom left">
        <a class="item" href="?order=' . $order . '&amp;act=edit_screens"><i class="fa fa-image fa-fw"></i></a>
            </span>
            <span data-tooltip="' . __('Параметры файла') . '" data-position="bottom left">
        <a class="item" href="?order=' . $order . '&amp;act=edit_prop"><i class="fa fa-cog fa-fw"></i></a>
            </span>
            <span data-tooltip="' . __('Переместить файл') . '" data-position="bottom left">
        <a class="item" href="?order=' . $order . '&amp;act=edit_path"><i class="fa fa-arrows fa-fw"></i></a>
            </span>
            <span data-tooltip="' . __('Удалить файл') . '" data-position="bottom left">
        <a class="item" href="?order=' . $order . '&amp;act=edit_unlink"><i class="fa fa-trash-o fa-fw"></i></a>
            </span>
        </div>';

    $listing->display();
}

if (empty($_GET['act'])) {
    $screens_count = $file->getScreensCount();
    $query_screen = (int) @$_GET['screen_num'];
    if ($screens_count) {
        if ($query_screen < 0 || $query_screen >= $screens_count)
            $query_screen = 0;

        if ($screen = $file->getScreen($doc->img_max_width(), $query_screen)) {
            echo "<img class='photo' src='" . $screen . "' alt='" . __('Скриншот') . " $query_screen' /><br />\n";
        }

        if ($screens_count > 1) {
            $select = array();

            for ($i = 0; $i < $screens_count; $i++) {
                $select[] = array('?order=' . $order . '&amp;screen_num=' . $i, $i + 1, $query_screen == $i);
            }

            $show = new design();
            $show->assign('select', $select);
            $show->display('design.select_bar.tpl');
        }
    }

    $listing = new ui_components();
    $listing->ui_segment = true; //подключаем css segments
    $listing->class = 'ui segments';

    if ($description = $file->description) {
        $post = $listing->post();
        $post->class = 'ui segment';
        $post->list = true;
        $post->title = $description;
    }

    if ($title = $file->title) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Заголовок');
        $post->content[] = $title;
    }

    if ($artist = $file->artist) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Исполнители');
        $post->content[] = $artist;
        $doc->keywords[] = $artist;
    }

    if ($band = $file->band) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Группа');
        $post->content[] = $band;
        $doc->keywords[] = $band;
    }

    if ($album = $file->album) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Альбом');
        $post->content[] = $album;
        $doc->keywords[] = $album;
    }

    if ($year = $file->year) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Год') . ": $year";
    }

    if ($genre = $file->genre) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Жанр');
        $post->content[] = $genre;
    }

    if ($comment = $file->comment) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Комментарий');
        $post->content[] = $comment;
    }

    if ($track_number = (int) $file->track_number) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Номер трека') . ": $track_number";
    }

    if ($language = $file->language) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Язык');
        $post->content[] = $language;
    }

    if ($url = $file->url) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Ссылка');
        $post->content[] = $url;
    }

    if ($copyright = $file->copyright) {
        $post = $listing->post();
        $post->class = 'ui segment';
        $post->list = true;
        $post->title = __('Копирайт');
        $post->content[] = $copyright;
    }

    if ($vendor = $file->vendor) {
        $post = $listing->post();
        $post->class = 'ui segment';
        $post->list = true;
        $post->title = __('Производитель');
        $post->content[] = $vendor;
    }

    if (($width = (int) $file->width) && ($height = (int) $file->height)) {
        $post = $listing->post();
        $post->class = 'ui segment';
        $post->list = true;
        $post->title = __('Разрешение') . ": $width x $height";
    }

    if ($frames = (int) $file->frames) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Кол-во кадров') . ": $frames";
    }

    if ($playtime_string = $file->playtime_string) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Продолжительность') . ": $playtime_string";
    }

    if (($video_bitrate = (int) $file->video_bitrate) && ($video_bitrate_mode = $file->video_bitrate_mode)) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Видео битрейт') . ": " . misc::getDataCapacity($video_bitrate) . "/s (" . $video_bitrate_mode . ")";
    }

    if ($video_codec = $file->video_codec) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Видео кодек') . ": $video_codec";
    }

    if ($video_frame_rate = $file->video_frame_rate) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Частота') . ": " . __('%s кадров в секунду', round($video_frame_rate / 60));
    }

    if (($audio_bitrate = (int) $file->audio_bitrate) && ($audio_bitrate_mode = $file->audio_bitrate_mode)) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Аудио битрейт') . ": " . misc::getDataCapacity($audio_bitrate) . "/s (" . $audio_bitrate_mode . ")";
    }

    if ($audio_codec = $file->audio_codec) {
        $post = $listing->post();
        $post->list = true;
        $post->class = 'ui segment';
        $post->title = __('Аудио кодек') . ": $audio_codec";
    }

    if ($file->id_user) {
        $ank = new user($file->id_user);

        $post = $listing->post();
        $post->class = 'ui segment';
        $post->list = true;
        $post->title = __('Файл загрузил' . ($ank->sex ? '' : 'а')) . " $ank->nick " . misc::times($file->time_add);
        $post->url = '/profile.view.php?id=' . $ank->id;
    }

    $post = $listing->post();
    $post->class = 'ui segment';
    $post->list = true;
    $post->title = __('Размер файла') . ": " . misc::getDataCapacity($file->size);

    $post = $listing->post();
    $post->list = true;
    $post->class = 'ui segment';
    $post->title = __('Общая оценка');
    $post->content[] = $file->rating_name . ' (' . round($file->rating, 1) . '/' . $file->rating_count . ")";

    $listing->display();


    if ($user->group && $file->id_user != $user->id) {
        $my_rating = @implode('', $file->rating_my()); // мой рейтинг
        $form = new design();
        $form->assign('method', 'post');
        $form->assign('action', '?order=' . $order . '&amp;screen_num=' . $query_screen . '&amp;' . passgen());
        $elements = array();
        $options = array();

        foreach ($file->ratings AS $rating => $rating_name) {
            $options[] = array($rating, $rating_name, $rating == $my_rating);
        }

        $elements[] = array('type' => 'select', 'title' => __('Оцените файл'), 'br' => 1, 'info' => array('name' => 'rating', 'options' => $options));
        $elements[] = array('type' => 'submit', 'br' => 0, 'info' => array('name' => 'save', 'value' => __('Оценить'), 'class' => 'tiny ui blue button')); // кнопка
        $form->assign('el', $elements);
        $form->display('input.form.tpl');
    }

    $form = new form();
    $form->text('url', __('Скопировать ссылку'), 'http://' . $_SERVER['HTTP_HOST'] . '/files' . $file->getPath());
    $form->display();

    $form = new form('/files' . $file->getPath());
    $form->hidden('rnd', passgen());
    $form->block('<div class="ui labeled button" tabindex="0">');
    $form->button(__('Скачать'), false, false, 'tiny ui blue button', 'fa fa-download fa-fw');
    $form->block('<span class="ui basic blue left pointing label">');
    $form->block(intval($file->downloads) . ' ' . __(misc::number($file->downloads, 'раз', 'раза', 'раз')));
    $form->block('</span>');
    $form->block('</div>');
    $form->display();
}

$pages = new pages;
$res = $db->prepare("SELECT COUNT(*) FROM `files_comments` WHERE `id_file` = ?");
$res->execute(Array($file->id));
$pages->posts = $res->fetchColumn();

$can_write = true;
if (!$user->is_writeable) {
    $doc->msg(__('Писать запрещено'), 'write_denied');
    $can_write = false;
}

if ($can_write && $pages->this_page == 1) {
    if ($can_write && isset($_POST['send']) && isset($_POST['message']) && isset($_POST['token']) && $user->group) {
        $message = (string) $_POST['message'];
        $users_in_message = text::nickSearch($message);
        $message = text::input_text($message);

        if (!antiflood::useToken($_POST['token'], 'files')) {
            // повторная отправка формы
            // вывод сообщений, возможно, будет лишним
        } else if ($dcms->censure && $mat = is_valid::mat($message)) {
            $doc->err(__('Обнаружен мат: %s', $mat));
        } elseif ($message) {
            $user->balls += $dcms->add_balls_comment_file;
            $res = $db->prepare("INSERT INTO `files_comments` (`id_file`, `id_user`, `time`, `text`) VALUES (?,?,?,?)");
            $res->execute(Array($file->id, $user->id, TIME, $message));
            $doc->msg(__('Комментарий успешно оставлен'));

            $id_message = $db->lastInsertId();
            if ($users_in_message) {
                for ($i = 0; $i < count($users_in_message) && $i < 20; $i++) {
                    $user_id_in_message = $users_in_message[$i];
                    if ($user_id_in_message == $user->id || ($file->id_user && $file->id_user == $user_id_in_message)) {
                        continue;
                    }
                    $ank_in_message = new user($user_id_in_message);
                    if ($ank_in_message->notice_mention) {
                        $ank_in_message->not(($user->sex ? 'упомянул' : 'упомянула') . " о вас в комментарии к файлу [url=/files{$file->getPath()}.htm#comment{$id_message}]$file->runame[/url]", $user->id);
                    }
                }
            }

            $file->comments++;

            if ($file->id_user && $file->id_user != $user->id) { // уведомляем автора о комментарии
                $ank = new user($file->id_user);
                $ank->not(($user->sex ? 'оставил' : 'оставила') . " комментарий к вашему файлу [url=/files{$file->getPath()}.htm]$file->runame[/url]", $user->id);
            }
            if ($doc instanceof document_json) {
                $doc->form_value('message', '');
                $doc->form_value('token', antiflood::getToken('files'));
            }

            exit;
        } else {
            $doc->err(__('Сообщение пусто'));
        }

        if ($doc instanceof document_json) {
            $doc->form_value('token', antiflood::getToken('files'));
        }
    }


    if ($user->group) {
// форма добавления комментария
        $message_form = '';
        if (isset($_GET['message']) && is_numeric($_GET['message'])) {
            $id_message = (int) $_GET['message'];
            $q = $db->prepare("SELECT * FROM `files_comments` WHERE `id` = ? LIMIT 1");
            $q->execute(Array($id_message));
            if ($message = $q->fetch()) {
                $anks = new user($message['id_user']);
                if (isset($_GET['reply'])) {
                    $message_form = '@' . $anks->login . ', ';
                } elseif (isset($_GET['quote'])) {
                    $message_form = "[quote id_user=\"{$anks->id}\" time=\"{$message['time']}\"]{$message['text']}[/quote] ";
                }
            }
        }

        if (empty($_GET['act'])) {
            if (!AJAX) {
                $form = new form(new url(null, array('screen_num' => $query_screen)));
                $form->refresh_url(new url(null, array('screen_num' => $query_screen)));
                $form->setAjaxUrl(new url(null, array('screen_num' => $query_screen)));
                $form->hidden('token', antiflood::getToken('files'));
                $form->textarea('message', __('Комментарий'), $message_form, true);
                $form->button(__('Отправить'), 'send', false);
                $form->display();
            }
        }
    }
}

if (empty($_GET['act'])) {
    // комменты будут отображаться только когда над файлом не производится никаких действий

    if (!empty($_GET['delete_comm']) && $user->group >= $file->group_edit) {
        $delete_comm = (int) $_GET['delete_comm'];
        $res = $db->prepare("SELECT COUNT(*) FROM `files_comments` WHERE `id` = ? AND `id_file` = ?");
        $res->execute(Array($delete_comm, $file->id));
        $k = $res->fetchColumn();
        if ($k) {
            $res = $db->prepare("DELETE FROM `files_comments` WHERE `id` = ? LIMIT 1");
            $res->execute(Array($delete_comm));
            $file->comments--;
            $doc->msg(__('Комментарий успешно удален'));
        } else
            $doc->err(__('Комментарий уже удален'));
    }

    $listing = new ui_components();
    $listing->ui_comment = true; //подключаем css comments
    $listing->ui_segment = true; //подключаем css segments
    $listing->class = $dcms->browser_type == 'full' ? 'ui minimal comments large segments' : 'ui comments large segments';

    if (!empty($form)) {
        $listing->setForm($form);
    }

    $q = $db->prepare("SELECT * FROM `files_comments` WHERE `id_file` = ? ORDER BY `id` DESC LIMIT " . $pages->limit);
    $q->execute(Array($file->id));

    $after_id = false;
    if ($arr = $q->fetchAll()) {
        foreach ($arr AS $comment) {

            $ank = new user($comment['id_user']);

            $post = $listing->post();
            $post->class = 'ui segment comment';
            $post->comments = true;
            $post->id = 'files_post_' . $comment['id'];
            $post->url = '/profile.view.php?id=' . $ank->id;
            $post->login = $ank->nick();
            $post->time = misc::times($comment['time']);
            $post->content = text::toOutput($comment['text']);
            $post->avatar = $ank->getAvatar();
            $post->image_a_class = 'avatar';

            if ($user->group && ($user->id != $ank->id)) {
                $post->action(false, '?order=' . $order . '&amp;screen_num=' . $query_screen . '&amp;message=' . $comment['id'] . '&amp;reply', __('Ответить'));
            }
            if ($user->group) {
                $post->action(false, '?order=' . $order . '&amp;screen_num=' . $query_screen . '&amp;message=' . $comment['id'] . '&amp;quote', __('Цитировать'));
            }
            if ($user->group >= $file->group_edit) {
                $post->action(false, '?order=' . $order . '&amp;screen_num=' . $query_screen . '&amp;delete_comm=' . $comment['id'], __('Удалить'));
            }

            if ($doc instanceof document_json) {
                $doc->add_post($post, $after_id);
            }

            $after_id = $post->id;
        }
    }

    if ($doc instanceof document_json && !$arr) {
        $post = new ui_compost(__('Нет результатов'));
        $post->icon('clone');
        $doc->add_post($post);
    }

    $listing->setAjaxUrl('?order=' . $order . '&amp;screen_num=' . $query_screen . '&amp;');
    $listing->display(__('Нет результатов'));
    $pages->display('?order=' . $order . '&amp;screen_num=' . $query_screen . '&amp;'); // вывод страниц

    if ($doc instanceof document_json) {
        $doc->set_pages($pages);
    }
}
//endregion
// переход к рядом лежащим файлам в папке
$content = $dir->getList($order);
$files = &$content['files'];
$count = count($files);

if ($count > 1) {
    for ($i = 0; $i < $count; $i++) {
        if ($file->name == $files[$i]->name)
            $fileindex = $i;
    }

    if (isset($fileindex)) {
        $select = array();

        if ($fileindex >= 1) {
            $last_index = $fileindex - 1;
            $select[] = array('./' . urlencode($files[$last_index]->name) . '.htm?order=' . $order, text::toValue($files[$last_index]->runame));
        }

        $select[] = array('?order=' . $order, text::toValue($file->runame), true);

        if ($fileindex < $count - 1) {
            $next_index = $fileindex + 1;
            $select[] = array('./' . urlencode($files[$next_index]->name) . '.htm?order=' . $order, text::toValue($files[$next_index]->runame));
        }

        $show = new design();
        $show->assign('select', $select);
        $show->display('design.select_bar.tpl');
    }
}

$doc->ret($dir->runame, './?order=' . $order); // возвращение в папку
$return = $dir->ret(5); // последние 5 ссылок пути
for ($i = 0; $i < count($return); $i++) {
    $doc->ret($return[$i]['runame'], '/files' . $return[$i]['path']);
}

if ($access_edit) {
    include 'inc/file_form.php';
}
exit;
