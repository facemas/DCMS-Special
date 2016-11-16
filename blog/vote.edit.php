<?php

include_once '../sys/inc/start.php';
$doc = new document(1);

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
if (!$blogs = $q->fetch()) {
    if (isset($_GET ['return'])) {
        header('Refresh: 1; url=' . $_GET ['return']);
    } else {
        header('Refresh: 1; url=./');
    }
    $doc->err(__('Записи не существует'));
    exit;
}
$doc->title = __($blogs['name'] . ' : Ред. Голосования');

$autor = new user((int) $blogs['autor']);
if ($autor->id == $user->id || $user->group >= 2) {

    if (empty($blogs['id_vote'])) {
        $doc->toReturn(new url('/blog/blog.php', array('blog', $blogs['id'])));
        $doc->err(__('Голосование отсутствует'));
        exit;
    }

    $q = $db->prepare("SELECT * FROM `blog_vote` WHERE `id` = ?");
    $q->execute(Array($blogs['id_vote']));

    if (!$vote_a = $q->fetch()) {
        $doc->toReturn(new url('/blog/blog.php', array('blog', $blogs['id'])));
        $doc->err(__('Голосование отсутствует'));
        exit;
    }

    if (!empty($_POST['vote'])) {
        $vote = text::input_text($_POST['vote']);
        if (!$vote) {
            $doc->err(__('Поле "Вопрос" пусто'));
        } else {
            $set = array();
            foreach ($_POST as $key => $value) {
                $vv = text::input_text($value);
                if ($vv && preg_match('#^v([0-9]+)$#', $key, $m)) {
                    if ($m[1] > 0 || $m[1] <= 10) {
                        $set[] = "`v$m[1]` = " . $db->quote($vv);
                    }
                }
            }
            $num = count($set);
            for ($i = 0; $i < (10 - $num); $i++) {
                $set[] = "`v" . (10 - $i) . "` = null";
            }

            if (count($set) < 2) {
                $doc->err(__('Должно быть не менее 2-х вариантов ответа'));
            } else {
                // echo mysql_error();
                $doc->toReturn(new url('/blog/blog.php?blog=' . $blogs['id']));

                if (!empty($_POST['finish'])) {
                    $res = $db->prepare("UPDATE `blog_vote` SET `active` = '0' WHERE `id` = ? LIMIT 1");
                    $res->execute(Array($vote_a['id']));
                    $dcms->log('Форум', 'Закрытие голосования в теме [url=/blog/blog.php?blog=' . $blogs['id'] . ']' . $blogs['name'] . '[/url]');
                    $doc->msg(__('Голосование окончено'));
                } elseif (!empty($_POST['clear'])) {
                    $res = $db->prepare("UPDATE `blog_vote` SET `active` = '1' WHERE `id` = ? LIMIT 1");
                    $res->execute(Array($vote_a['id']));
                    $res = $db->prepare("DELETE FROM `blog_vote_votes` WHERE `id_vote` = ?");
                    $res->execute(Array($vote_a['id']));
                    $dcms->log('Форум', 'Обнуление голосования в теме [url=/blog/blog.php?blog=' . $blogs['id'] . ']' . $blogs['name'] . '[/url]');
                    $doc->msg(__('Голосование начато заново'));
                } elseif (!empty($_POST['delete'])) {
                    $res = $db->prepare("DELETE FROM `blog_vote`  WHERE `id` = ? LIMIT 1");
                    $res->execute(Array($vote_a['id']));
                    $res = $db->prepare("DELETE FROM `blog_vote_votes` WHERE `id_vote` = ?");
                    $res->execute(Array($vote_a['id']));
                    $res = $db->prepare("UPDATE `blog` SET `id_vote` = null WHERE `id` = ? LIMIT 1");
                    $res->execute(Array($blogs['id']));
                    $dcms->log('Форум', 'Удаление голосования в теме [url=/blog/blog.php?blog=' . $blogs['id'] . ']' . $blogs['name'] . '[/url]');
                    $doc->msg(__('Голосование успешно удалено'));
                } else {
                    $dcms->log('Форум', 'Изменение параметров голосования в теме [url=/blog/blog.php?blog=' . $blogs['id'] . ']' . $blogs['name'] . '[/url]');
                    $res = $db->prepare("UPDATE `blog_vote` SET " . implode(', ', $set) . ", `name` = ? WHERE `id` = ? LIMIT 1");
                    $res->execute(Array($vote, $vote_a['id']));
                    $doc->msg(__('Параметры успешно изменены'));
                }

                if (isset($_GET['return']))
                    $doc->ret('В тему', text::toValue($_GET['return']));
                else
                    $doc->ret(__('Вернуться'), '/blog/blog.php?blog=' . $blogs['id']);
                exit;
            }
        }
    }

    $form = new form('?id=' . $id_blog . '&amp;' . passgen());
    $form->textarea('vote', __('Вопрос'), $vote_a['name']);

    for ($i = 1; $i <= 10; $i++) {
        $form->text("v$i", __('Ответ №') . $i, $vote_a['v' . $i]);
    }
    $form->checkbox('finish', __('Окончить голосование'));
    $form->checkbox('clear', __('Начать заново'));
    $form->checkbox('delete', __('Удалить голосование'));
    $form->button(__('Применить'));
    $form->display();
} else {
    $doc->err(__('Доступ ограничен'));
}
if (isset($_GET['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('Вернуться'), '/blog/blog.php?blog=' . $blogs['id']);
}
