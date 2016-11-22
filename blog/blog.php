<?php

include_once '../sys/inc/start.php';

if (AJAX) {
    $doc = new document_json();
} else {
    $doc = new document();
}

if (!isset($_GET ['blog']) || !is_numeric($_GET ['blog'])) {
    if (isset($_GET ['return'])) {
        header('Refresh: 1; url=' . $_GET ['return']);
    } else {
        header('Refresh: 1; url=./');
    }

    $doc->err(__('Запись не выбрана'));
    exit();
}
$id_blog = (int) $_GET['blog'];
$q = $db->prepare("SELECT `blog`.* , `blog_cat`.`name` AS `cat_name` FROM `blog` LEFT JOIN `blog_cat` ON `blog_cat`.`id` = `blog`.`id_cat` WHERE `blog`.`id` = ?");
$q->execute(Array($id_blog));

if (!$blogs = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Записи не существует'));
    exit;
}
$doc->title = $blogs['name'];
$doc->description = $blogs['message'];

if ($blogs['block'] == 0) {
    if ($user->group) {
        $q = $db->prepare("SELECT * FROM `blog_views` WHERE `id_blog` = ? AND `id_user` = ? AND `time` > ?");
        $q->execute(Array($blogs['id'], $user->id, DAY_TIME));
        if (!$q->fetch()) {
            $res = $db->prepare("INSERT INTO `blog_views` (`id_blog`, `id_user`, `time`) VALUES (?, ?, ?)");
            $res->execute(Array($blogs['id'], $user->id, (TIME + 1)));
        } else {
            $res = $db->prepare("UPDATE `blog_views` SET `time` = ? WHERE `id_blog` = ? AND `id_user` = ? ORDER BY `time` DESC LIMIT 1");
            $res->execute(Array((TIME + 1), $blogs['id'], $user->id));
        }
    }

    $ank = new user((int) $blogs['autor']);
    $like = $db->query("SELECT * FROM `blog_like` WHERE `id_blog` = '" . intval($blogs['id']) . "'")->fetchAll();

    if (isset($_GET['like']) && $user->id) {
        $doc->toReturn(new url('/blog/blog.php?blog=' . $blogs['id']));
        $qq = $db->query("SELECT * FROM `blog_like` WHERE `id_user` = '" . intval($user->id) . "' AND `id_blog` = '" . intval($blogs['id']) . "' LIMIT 1")->fetch();

        if (!$qq) {
            $res = $db->prepare("INSERT INTO `blog_like` (`id_user`, `time`, `id_blog`) VALUES (?, ?, ?)");
            $res->execute(Array(intval($user->id), TIME, intval($blogs['id'])));
            #Уведомление
            $ank->not(($user->sex ? 'оценил' : 'оценила') . " ваш блог [url=/blog/blog.php?blog=" . $blogs['id'] . "]$blogs[name][/url]", $user->id);
            $doc->msg(__('Вы успешно оценили блог'));

            if (isset($_GET['return'])) {
                $doc->ret('В блог', text::toValue($_GET['return']));
            }
        } else {
            $doc->err(__('Лайк уже засчитан'));

            if (isset($_GET['return'])) {
                $doc->ret('В блог', text::toValue($_GET['return']));
            }
        }
    }

    if ($user->group >= 2 || $user->id == $ank->id) {

        $listing = new ui_components();
        $listing->ui_menu = true;

        $post = $listing->post();
        $post->head = '<div class="ui icon menu">
            ' . (!$blogs['id_vote'] ? '
            <span data-tooltip="' . __('Создать голосование') . '" data-position="bottom left">
                <a class="item" href="vote.new.php?id=' . $blogs['id'] . '"><i class="fa fa-bar-chart fa-fw"></i></a>
            </span>
            ' : '
            <span data-tooltip="' . __('Редактировать голосование') . '" data-position="bottom left">
                <a class="item" href="vote.edit.php?id=' . $blogs['id'] . '"><i class="fa fa-bar-chart fa-fw"></i></a>
            </span>
            ') . '
            
            <span data-tooltip="' . __('Файлы блога') . '" data-position="bottom left">
                <a class="item" href="blog.files.php?id=' . $blogs['id'] . '"><i class="fa fa-file fa-fw"></i></a>
            </span>
            
            <span data-tooltip="' . __('Очистить блог') . '" data-position="bottom left">
                <a class="item" href="message.delete_all.php?id=' . $blogs['id'] . '"><i class="fa fa-window-close-o fa-fw"></i></a>
            </span>
            
            <span data-tooltip="' . __('Редактировать блог') . '" data-position="bottom left">
                <a class="item" href="blog.edit.php?id=' . $blogs['id'] . '"><i class="fa fa-cog fa-fw"></i></a>
            </span>
            
            <span data-tooltip="' . __('Удалить блог') . '" data-position="bottom left">
                <a class="item" href="blog.delete.php?id=' . $blogs['id'] . '"><i class="fa fa-trash-o fa-fw"></i></a>
            </span>
            ' . ($user->group >= 2 ? '
            <span data-tooltip="' . __('Заблокировать блог') . '" data-position="bottom left">
                <a class="item" href="blog.block.php?id=' . $blogs['id'] . '"><i class="fa fa-lock fa-fw"></i></a>
            </span>
            ' : null) . '
        </div>';

        $listing->display();
    }

    $listing = new ui_components();
    $listing->ui_comment = true; //подключаем css comments
    $listing->ui_segment = true; //подключаем css segment
    $listing->ui_list = true; //подключаем css list
    $listing->class = $dcms->browser_type == 'full' ? 'segments minimal comments' : 'segments comments';

    $post = $listing->post();
    $post->class = 'ui segment comment';
    $post->comments = true;

    $post->content = text::toOutput($blogs['message']);
    $post->login = "<b>" . text::toValue($blogs['name']) . "</b>";
    $post->time = misc::when($blogs['time_create']);
    $post->avatar = $ank->getAvatar(80);
    $post->image_a_class = 'ui avatar';

    $post = $listing->post();
    $post->class = 'ui secondary segment comment';
    $post->comments = true;
    $autor = $ank->nick();

    # Счетчик лайков
    $res = $db->prepare("SELECT COUNT(*) FROM `blog_like` WHERE `id_blog` = ?");
    $res->execute(Array(intval($blogs['id'])));
    $like = $res->fetchColumn();

    # Счетчик просмотров
    $res = $db->prepare("SELECT COUNT(*) FROM `blog_views` WHERE `id_blog` = ?");
    $res->execute(Array(intval($blogs['id'])));
    $views = $res->fetchColumn();

    $post->bottom .= '<div class="ui very relaxed horizontal list"> ';

    # Комментарии
    $post->bottom .= '<div class="item"><div class="content"><a class="header" data-tooltip="' . __('Комментариев %s', $blogs['comm']) . '" data-position="top left"><i class="fa fa-comments fa-fw"></i> ' . $blogs['comm'] . '</a></div></div> ';
    # Просмотры
    $post->bottom .= '<div class="item"><div class="content"><a href="blog.views.php?id=' . $blogs['id'] . '" class="header" data-tooltip="' . __('Просмотров %s', $views) . '" data-position="top center"><i class="fa fa-eye fa-fw"></i> ' . $views . '</a></div></div> ';
    # Мне нравится
    $stt = $db->query("SELECT * FROM `blog_like` WHERE `id_user` = '$user->id' AND `id_blog` = '" . intval($blogs['id']) . "' LIMIT 1")->fetch();

    if ($user->id && $user->id != $ank->id && !$stt) {
        $post->bottom .= '<div class="item"><div class="content"><a href="?blog=' . $blogs['id'] . '&amp;like" data-tooltip="' . __('Мне нравится') . '" data-position="top center" class="header"><i class="fa fa-heart-o fa-fw"></i> ' . $like . '</a></div></div>';
    } elseif ($user->id && $user->id != $ank->id) {
        $post->bottom .= '<div class="item"><div class="content"><a href="blog.like.php?id=' . $blogs['id'] . '" class="header" data-tooltip="' . __('Вам понравилось') . '" data-position="top center"><span style="color: #e81c4f"><i class="fa fa-heart fa-fw"></i> ' . $like . '</span></a></div></div>';
    } else {
        $post->bottom .= '<div class="item"><div class="content"><a href="blog.like.php?id=' . $blogs['id'] . '" class="header" data-tooltip="' . __('Оценили %s', $like) . '" data-position="top center"><i class="fa fa-heart fa-fw"></i> ' . $like . '</a></div></div>';
    }
    $post->bottom .= '<div class="item"><div class="content"><a href="/profile.view.php?id=' . $ank->id . '" class="header" data-tooltip="' . __('Автор') . '" data-position="top center">' . $autor . '</a></div></a></div>';
    $post->bottom .= '</div>';
    $listing->display();

    $post_dir_path = H . '/sys/files/.blog/' . $blogs['id'];
    if (@is_dir($post_dir_path)) {
        $dir = new files($post_dir_path);
        $content = $dir->getList('time_add:asc');
        $files = &$content['files'];
        $count = count($files);

        if ($files) {
            $listing = new ui_components();
            $listing->ui_segment = true; //подключаем css segment
            $listing->ui_list = true; //подключаем css list
            $listing->class = 'ui segments';

            $post = $listing->post();
            $post->class = 'ui secondary segment';
            $post->title = __('Прикрепленные файлы:');
            $post->icon('file');

            for ($i = 0; $i < $count; $i++) {
                $post = $listing->post();
                $post->class = 'ui segment';
                $post->list = true;
                $post->title = text::toValue($files[$i]->runame) . ' - <span style="color: green">' . misc::getDataCapacity($files[$i]->size) . '</span> ';
                $post->url = "/files" . $files[$i]->getPath() . ".htm?order=time_add:asc";
                $post->icon($files[$i]->icon());
                $post->image = $files[$i]->image();
            }


            $listing->display();
        }
    }

    # Выводим голосования, если есть
    include 'blog.votes.php';

    $pages = new pages($db->query("SELECT COUNT(*) FROM `blog_comment`  WHERE `blog` = '" . $blogs['id'] . "'")->fetchColumn());

    $can_write = true;

    if (!$user->is_writeable) {
        $doc->msg(__('Писать запрещено'), 'write_denied');
        $can_write = false;
    }

    if ($can_write && $pages->this_page == 1) {
        if (isset($_POST['send']) && isset($_POST['message']) && isset($_POST['token']) && $user->group) {
            $message = (string) $_POST['message'];
            $users_in_message = text::nickSearch($message);
            $message = text::input_text($message);

            if (!antiflood::useToken($_POST['token'], 'blog_comment')) {
                // нет токена (обычно, повторная отправка формы)
            } elseif ($dcms->censure && $mat = is_valid::mat($message)) {
                $doc->err(__('Обнаружен мат: %s', $mat));
            } elseif ($message) {
                //$user->balls += $dcms->add_balls_chat ;

                $qe = $db->prepare("INSERT INTO `blog_comment` (`id_user`, `time`, `mess`, `blog`) VALUES (?,?,?,?)");
                $qe->execute(array($user->id, TIME, $message, $blogs['id']));
                $qr = $db->prepare("UPDATE `blog` SET `comm` = `comm`+1 WHERE `id` = ? LIMIT 1");
                $qr->execute(array($blogs['id']));

                header('Refresh: 1; url=?blog=' . $blogs['id'] . '&amp;' . passgen() . '&' . SID);
                if ($users_in_message) {
                    for ($i = 0; $i < count($users_in_message) && $i < 20; $i++) {
                        $user_id_in_message = $users_in_message[$i];
                        if ($user_id_in_message == $user->id || ($blogs['autor'] && $blogs['autor'] == $user_id_in_message)) {
                            continue;
                        }
                        $ank_in_message = new user($user_id_in_message);
                        if ($ank_in_message->notice_mention) {
                            $ank_in_message->not(($user->sex ? 'Упомянул' : 'Упомянула') . " о Вас в комментарии к блогу [url=/blog/blog.php?blog=" . $blogs['id'] . "]" . $blogs['name'] . "[/url]", $user->id);
                        }
                    }
                }
                if ($blogs['autor'] != $user->id) { // уведомляем автора о комментарии
                    $ank = new user($blogs['autor']);
                    $ank->not(($user->sex ? 'Оставил' : 'Оставила') . " комментарий к Вашему блогу [url=/blog/blog.php?blog=" . $blogs['id'] . "]" . $blogs['name'] . "[/url]", $user->id);
                }
                $doc->ret(__('Вернуться'), '?blog=' . $blogs['id'] . '&amp;' . passgen());
                $doc->msg(__('Сообщение успешно отправлено'));
                if ($doc instanceof document_json) {
                    $doc->form_value('message', '');
                    $doc->form_value('token', antiflood::getToken('blog_comment'));
                }

                exit;
            } else {
                $doc->err(__('Сообщение пусто'));
            }

            if ($doc instanceof document_json) {
                $doc->form_value('token', antiflood::getToken('blog_comment'));
            }
        }

        if ($user->group) {
            $message_form = '';
            if (isset($_GET ['message']) && is_numeric($_GET ['message'])) {
                $id_message = (int) $_GET ['message'];
                $q = $db->prepare("SELECT * FROM `blog_comment` WHERE `id` = ? LIMIT 1");
                $q->execute(Array($id_message));
                if ($message = $q->fetch()) {
                    $ank = new user($message['id_user']);
                    if (isset($_GET['reply'])) {
                        $message_form = '@' . $ank->login . ', ';
                    } elseif (isset($_GET['quote'])) {
                        $message_form = "@$ank->login, [quote id_user=\"{$ank->id}\" time=\"{$message['time']}\"]{$message['mess']}[/quote] ";
                    }
                }
            }

            if (!AJAX) {
                $form = new form('?blog=' . $blogs['id'] . '&amp;' . passgen());
                $form->refresh_url('?blog=' . $blogs['id'] . '&amp;' . passgen());
                $form->setAjaxUrl('?blog=' . $blogs['id'] . '&amp;' . passgen());
                $form->hidden('token', antiflood::getToken('blog_comment'));
                $form->textarea('message', __('Комментарий'), $message_form, true);
                $form->button(__('Отправить'), 'send', false);
                $form->display();
            }
        }
    }

    $listing = new ui_components();
    $listing->ui_comment = true; //подключаем css comments
    $listing->ui_segment = true; //подключаем css segments
    $listing->class = $dcms->browser_type == 'full' ? 'segments minimal large comments' : 'segments small comments';


    if (!empty($form)) {
        $listing->setForm($form);
    }

    $q = $db->prepare("SELECT * FROM `blog_comment` WHERE `blog`= ? ORDER BY `id` DESC LIMIT $pages->limit");
    $q->execute(array($blogs['id']));

    $after_id = false;

    if ($arr = $q->fetchAll()) {
        foreach ($arr AS $message) {
            $anks = new user($message['id_user']);

            $post = $listing->post();
            $post->class = 'ui segment comment';
            $post->comments = true;

            $post->id = 'chat_post_' . $message['id'];
            $post->url = 'actions.php?idblog=' . $id_blog . '&amp;id=' . $message['id'];
            $post->avatar = $anks->getAvatar();
            $post->image_a_class = 'ui avatar';
            $post->time = misc::timek($message['time']);
            $post->login = $anks->nick();
            $post->content = text::toOutput($message['mess']);

            if ($user->group && ($user->id != $anks->id)) {
                $post->action(false, "?blog=$blogs[id]&amp;message=$message[id]&amp;reply", __('Ответить'));
            }
            if ($user->group) {
                $post->action(false, "?blog=$blogs[id]&amp;message=$message[id]&amp;quote", __('Цитировать'));
            }
            if ($user->group >= 2) {
                $post->action(false, "message.delete.php?idblog=$blogs[id]&amp;id=$message[id]", __('Удалить'));
            }

            if (!$doc->last_modified) {
                $doc->last_modified = $message['time'];
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

    $listing->setAjaxUrl('?blog=' . $blogs['id'] . '&amp;page=' . $pages->this_page);
    $listing->display(__('Нет результатов'));
    $pages->display('?blog=' . $blogs['id'] . '&amp;'); // вывод страниц

    if ($doc instanceof document_json) {
        $doc->set_pages($pages);
    }
} else {

    if ($user->group >= 2) {

        $listing = new ui_components();
        $listing->ui_menu = true;

        $post = $listing->post();
        $post->head = '<div class="ui icon menu">
            <span data-tooltip="' . __('Редактировать блог') . '" data-position="bottom left">
                <a class="item" href="blog.edit.php?id=' . $blogs['id'] . '"><i class="fa fa-cog fa-fw"></i></a>
            </span>
            
            <span data-tooltip="' . __('Удалить блог') . '" data-position="bottom left">
                <a class="item" href="blog.delete.php?id=' . $blogs['id'] . '"><i class="fa fa-trash-o fa-fw"></i></a>
            </span>

            <span data-tooltip="' . __('Разблокировать блог') . '" data-position="bottom left">
                <a class="item" href="blog.block.php?id=' . $blogs['id'] . '"><i class="fa fa-unlock fa-fw"></i></a>
            </span>
        </div>';

        $listing->display();
    }

    $listing = new ui_components();
    $listing->ui_segment = true; //подключаем css segments
    $listing->class = 'ui segments';

    $post = $listing->post();
    $post->class = 'ui secondary segment';
    $post->icon('book');
    $post->title = __('Запись заблокирована');

    $post = $listing->post();
    $post->class = 'ui segment';
    $post->list = true;
    $post->title = __('Причина') . ': ' . text::toOutput($blogs['prichina']);

    $listing->display();
}

$doc->ret($blogs['cat_name'], 'category.php?id=' . $blogs['id_cat']);
$doc->ret(__('Блоги'), 'index.php');
?>