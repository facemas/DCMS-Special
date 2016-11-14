<?php

include_once '../sys/inc/start.php';

$doc = new document(4);

if (!isset($_GET ['id']) || !is_numeric($_GET ['id'])) {
    if (isset($_GET ['return'])) {
        header('Refresh: 1; url=' . $_GET ['return']);
    } else {
        header('Refresh: 1; url=./');
    }

    $doc->err(__('Новость не выбрана'));
    exit();
}

$id_news = (int) $_GET['id'];

$q = $db->prepare("SELECT * FROM `news` WHERE `id` = ?");
$q->execute(Array($id_news));

if (!$news = $q->fetch()) {
    if (isset($_GET ['return'])) {
        header('Refresh: 1; url=' . $_GET ['return']);
    } else {
        header('Refresh: 1; url=./');
    }
    $doc->err(__('Новость не найдена или удалена'));
    exit;
}
$doc->title = __($news['title'] . ' : Ред. Голосования');
if ($user->group >= 4) {
    if (empty($news['id_vote'])) {
        $doc->toReturn(new url('/news/comments.php', array('id', $news['id'])));
        $doc->err(__('Голосование отсутствует'));
        exit;
    }
    $q = $db->prepare("SELECT * FROM `news_vote` WHERE `id` = ?");
    $q->execute(Array($news['id_vote']));

    if (!$vote_a = $q->fetch()) {
        $doc->toReturn(new url('/news/comments.php', array('id', $news['id'])));
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

                $doc->toReturn(new url('/news/comments.php?id=' . $news['id']));
                if (!empty($_POST['finish'])) {
                    $res = $db->prepare("UPDATE `news_vote` SET `active` = '0' WHERE `id` = ? LIMIT 1");
                    $res->execute(Array($vote_a['id']));
                    $dcms->log('Новости', 'Закрытие голосования в новости [url=/news/comments.php?id=' . $news['id'] . ']' . $news['title'] . '[/url]');
                    $doc->msg(__('Голосование окончено'));
                } elseif (!empty($_POST['clear'])) {
                    $res = $db->prepare("UPDATE `news_vote` SET `active` = '1' WHERE `id` = ? LIMIT 1");
                    $res->execute(Array($vote_a['id']));
                    $res = $db->prepare("DELETE FROM `news_vote_votes` WHERE `id_vote` = ?");
                    $res->execute(Array($vote_a['id']));
                    $dcms->log('Новости', 'Обнуление голосования в новости [url=/news/comments.php?id=' . $news['id'] . ']' . $news['title'] . '[/url]');
                    $doc->msg(__('Голосование начато заново'));
                } elseif (!empty($_POST['delete'])) {
                    $res = $db->prepare("DELETE FROM `news_vote`  WHERE `id` = ? LIMIT 1");
                    $res->execute(Array($vote_a['id']));
                    $res = $db->prepare("DELETE FROM `news_vote_votes` WHERE `id_vote` = ?");
                    $res->execute(Array($vote_a['id']));
                    $res = $db->prepare("UPDATE `news` SET `id_vote` = null WHERE `id` = ? LIMIT 1");
                    $res->execute(Array($news['id']));
                    $dcms->log('Новости', 'Удаление голосования в новости [url=/news/comments.php?id=' . $news['id'] . ']' . $news['title'] . '[/url]');
                    $doc->msg(__('Голосование успешно удалено'));
                } else {
                    $dcms->log('Новости', 'Изменение параметров голосования в новости [url=/news/comments.php?id=' . $news['id'] . ']' . $news['title'] . '[/url]');
                    $res = $db->prepare("UPDATE `news_vote` SET " . implode(', ', $set) . ", `name` = ? WHERE `id` = ? LIMIT 1");
                    $res->execute(Array($vote, $vote_a['id']));
                    $doc->msg(__('Изменения сохранены'));
                }

                if (isset($_GET['return'])) {
                    $doc->ret('В новость', text::toValue($_GET['return']));
                } else {
                    $doc->ret(__('Вернуться'), '/news/comments.php?id=' . $news['id']);
                }
                exit;
            }
        }
    }
    $form = new form('?id=' . $id_news . '&amp;' . passgen());
    $form->textarea('vote', __('Вопрос'), $vote_a['name']);

    for ($i = 1; $i <= 10; $i++) {
        $form->text("v$i", __('Ответ №') . $i, $vote_a['v' . $i]);
    }

    $form->checkbox('finish', __('Окончить голосование'));
    $form->checkbox('clear', __('Начать заново'));
    $form->checkbox('delete', __('Удалить голосование'));
    $form->button(__('Сохранить изменения'));
    $form->display();
} else {
    $doc->err(__('Доступ ограничен'));
}

if (isset($_GET['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('Вернуться'), '/news/comments.php?id=' . $news['id']);
}