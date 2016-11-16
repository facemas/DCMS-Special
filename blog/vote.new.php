<?php

include_once '../sys/inc/start.php';
$doc = new document(1);


if (!isset($_GET ['id']) || !is_numeric($_GET ['id'])) {
    if (isset($_GET ['return']))
        header('Refresh: 1; url=' . $_GET ['return']);
    else
        header('Refresh: 1; url=./');
    $doc->err(__('Запись не выбрана'));
    exit();
}
$id_blog = (int) $_GET ['id'];
$q = $db->prepare("SELECT * FROM `blog` WHERE `id` = ?");
$q->execute(Array($id_blog));
if (!$blogs = $q->fetch()) {
    if (isset($_GET ['return']))
        header('Refresh: 1; url=' . $_GET ['return']);
    else
        header('Refresh: 1; url=./');
    $doc->err(__('Записи не существует'));
    exit;
}
$doc->title = __($blogs['name'] . ' : Голосование');
$autor = new user((int) $blogs['autor']);
if ($autor->id == $user->id || $user->group >= 2) {
    if (!empty($blogs['id_vote'])) {
        $doc->toReturn(new url('/blog/blog.php?blog=' . $blogs['id']));
        $doc->err(__('Голосование уже создано'));
        exit;
    }

    if (!empty($_POST['vote'])) {
        $vote = text::input_text($_POST['vote']);
        if (!$vote) {
            $doc->err(__('Заполните поле "Вопрос"'));
        } else {
            $v = array();
            $k = array();
            foreach ($_POST as $key => $value) {
                $vv = text::input_text($value);
                if ($vv && preg_match('#^v([0-9]+)$#', $key)) {
                    $v[] = $db->quote($vv);
                    $k[] = '`v' . count($v) . '`';
                }
            }

            if (count($v) < 2) {
                $doc->err(__('Должно быть не менее 2-х вариантов ответа'));
            } else {
                $res = $db->prepare("INSERT INTO `blog_vote` (`id_autor`, `id_blog`, `name`, " . implode(', ', $k) . ") VALUES (?,?,?, " . implode(', ', $v) . ")");
                $res->execute(Array($user->id, $blogs['id'], $vote));

                if (!$id_vote = $db->lastInsertId()) {
                    $doc->err(__('При создании голосования возникла ошибка'));
                } else {
                    $doc->toReturn(new url('/blog/blog.php?blog=' . $blogs['id']));
                    $res = $db->prepare("UPDATE `blog` SET `id_vote` = ? WHERE `id` = ? LIMIT 1");
                    $res->execute(Array($id_vote, $blogs['id']));
                    $doc->msg('Голосование успешно создано');

                    $dcms->log('Форум', 'Создание голосования в теме [url=/blog/blog.php?blog=' . $blogs['id'] . ']' . $blogs['name'] . '[/url]');

                    if (isset($_GET['return'])) {
                        $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
                    } else {
                        $doc->ret(__('Вернуться'), '/blog/blog.php?blog=' . $blogs['id']);
                    }
                    exit;
                }
            }
        }
    }

    $form = new form(new url());
    $form->textarea('vote', __('Вопрос'));

    for ($i = 1; $i <= 10; $i++) {
        $form->text("v$i", __('Ответ №') . $i);
    }

    $form->button(__('Создать голосование'));
    $form->display();
} else {
    $doc->err(__('Доступ ограничен'));
}
if (isset($_GET['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('Вернуться'), '/blog/blog.php?blog=' . $blogs['id']);
}
