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
    $res = $db->prepare("SELECT COUNT(*) FROM `blog_view` WHERE `blog` = ? AND `id_user` = ? LIMIT 1");
    $res->execute(Array($blogs['id'], $user->id));
    $n = $res->fetchColumn();

    if ($user->group && ($n == 0)) {
        $q = $db->prepare("INSERT INTO `blog_view` (`id_user`, `blog`) VALUES (?,?)");
        $q->execute(array($user->id, $blogs['id']));
        $res = $db->prepare("UPDATE `blog` SET `view` = `view` + '1' WHERE `id` = ? LIMIT 1");
        $res->execute(Array($blogs['id']));
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
            $ank->not(($user->sex ? 'Оценил' : 'Оценила') . " Ваш блог [url=/blog/blog.php?blog=" . $blogs['id'] . "]$blogs[name][/url]", $user->id);
            $doc->msg(__('Вам понравилось'));

            if (isset($_GET['return'])) {
                $doc->ret('В тему', text::toValue($_GET['return']));
            }
        } else {
            $doc->err(__('Лайк уже засчитан'));

            if (isset($_GET['return'])) {
                $doc->ret('В тему', text::toValue($_GET['return']));
            }
        }
    }

    $listing = new listing();
    $post = $listing->post();

    if ($user->group >= 2 || $user->id == $ank->id) {
        $post->action('edit', "edit.blog.php?id=" . $blogs['id']);
        $post->action('trash-o', "delete.blog.php?id=" . $blogs['id']);
    }
    $post->content = text::toOutput($blogs['message']);
    $post->title = "<b>" . text::toValue($blogs['name']) . "</b>";
    $post->time = misc::when($blogs['time_create']);
    $post->image = $ank->getAvatar();

    $post = $listing->post();

    # Счетчик лайков
    $res = $db->prepare("SELECT COUNT(*) FROM `blog_like` WHERE `id_blog` = ?");
    $res->execute(Array(intval($blogs['id'])));
    $like = $res->fetchColumn();

    $autor = $ank->nick();

    # Комментарии
    $post->title .= ' <a class="btn btn-secondary btn-sm"><i class="fa fa-comments-o fa-fw"></i> ' . __('%s', $blogs['comm']) . '</a> ';
    # Просмотры
    $post->title .= ' <a class="btn btn-secondary btn-sm"><i class="fa fa-eye fa-fw"></i> ' . __('%s', $blogs['view']) . '</a> ';
    # Мне нравится
    $stt = $db->query("SELECT * FROM `blog_like` WHERE `id_user` = '$user->id' AND `id_blog` = '" . intval($blogs['id']) . "' LIMIT 1")->fetch();

    if ($user->id && $user->id != $ank->id && !$stt) {
        $post->title .= '<a href="?blog=' . $blogs['id'] . '&amp;like" class="btn btn-secondary btn-sm">' . __('Мне нравится') . '</a> <a href="like.php?id=' . $blogs['id'] . '" class="btn btn-secondary btn-sm"><i class="fa fa-thumbs-o-up fa-fw"></i> ' . __('%s', $like) . '</a>';
    } elseif ($user->id && $user->id != $ank->id) {
        $post->title .= '<a href="like.php?id=' . $blogs['id'] . '" class="btn btn-secondary btn-sm"><i class="fa fa-thumbs-o-up fa-fw"></i> ' . __('%s', $like) . '</a>';
    } else {
        $post->title .= '<a href="like.php?id=' . $blogs['id'] . '" class="btn btn-secondary btn-sm"><i class="fa fa-thumbs-o-up fa-fw"></i> ' . __('%s', $like) . '</a>';
    }
    $post->title .= ' <a href="/profile.view.php?id=' . $ank->id . '" class="btn btn-secondary btn-sm" style="float: right;">' . $autor . '</a>';

    $post_dir_path = H . '/sys/files/.blog/' . $blogs['id'];
    if (@is_dir($post_dir_path)) {
        $listing_files = new listing();
        $dir = new files($post_dir_path);
        $content = $dir->getList('time_add:asc');
        $files = &$content['files'];
        $count = count($files);
        for ($i = 0; $i < $count; $i++) {
            $file = $listing_files->post();
            $file->title = text::toValue($files[$i]->runame) . ' - ' . misc::getDataCapacity($files[$i]->size) . ' &nbsp; ' . $files[$i]->properties;
            $file->url = "/files" . $files[$i]->getPath() . ".htm?order=time_add:asc";
            $file->icon($files[$i]->icon());
            $file->image = $files[$i]->image();
        }
        if ($files) {
            $post = $listing->post();
            $post->title = __('Прикрепленные файлы:');
            $post->icon('file');
            $post->highlight = true;

            $post = $listing->post();
            $post->title = $listing_files->fetch();
        }
    }
    $listing->display();

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

    $listing = new listing();

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
            $post->id = 'blog_comment_' . $message['id'];
            $post->url = 'actions.php?idblog=' . $id_blog . '&amp;id=' . $message['id'];
            $post->time = misc::when($message['time']);
            $post->title = $anks->nick();
            $post->image = $anks->getAvatar();
            $post->post = text::toOutput($message['mess']);

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
        $post = new listing_post(__('Нет результатов'));
        $post->icon('clone');
        $doc->add_post($post);
    }

    $listing->setAjaxUrl('?blog=' . $blogs['id'] . '&amp;page=' . $pages->this_page);
    $listing->display(__('Нет результатов'));
    $pages->display('?blog=' . $blogs['id'] . '&amp;'); // вывод страниц

    if ($doc instanceof document_json) {
        $doc->set_pages($pages);
    }

    if ($user->group >= 2 || $user->id == $ank->id) {
        if ($blogs['id_vote']) {
            $doc->opt(__('Ред. голосование'), 'vote.edit.php?id=' . $blogs['id']);
        } else {
            $doc->opt(__('Создать голосование'), 'vote.new.php?id=' . $blogs['id']);
        }

        $doc->opt(__('Добавить файл'), 'files.blog.php?id=' . $blogs['id']);
        $doc->opt(__('Очистить блог'), 'message.delete_all.php?id=' . $blogs['id']);
    }
} else {
    $listing = new listing();
    $post = $listing->post();
    $post->hightlight = true;
    $post->icon('book');
    $post->title = __('Запись заблокирована');
    $post->post = 'Причина блокировки: ' . text::toOutput($blogs['prichina']);
    $listing->display();
}
if ($user->group >= 2) {
    if ($blogs['block'] == 0) {
        $doc->opt(__('Заблокировать'), 'block.blog.php?id=' . $blogs['id']);
    } else {

        $doc->opt(__('Редактировать'), 'edit.blog.php?id=' . $blogs['id']);
        $doc->opt(__('Удалить'), 'delete.blog.php?id=' . $blogs['id']);
        $doc->opt(__('Разблокировать'), 'block.blog.php?id=' . $blogs['id']);
    }
}

$doc->act($blogs['cat_name'], 'category.php?id=' . $blogs['id_cat']);
$doc->act(__('Блоги'), 'index.php');
?>