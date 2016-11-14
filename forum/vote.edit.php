<?php
include_once '../sys/inc/start.php';
$doc = new document();
$doc->title = __('Форум: Голосование');

if (!isset($_GET['id_theme']) || !is_numeric($_GET['id_theme'])) {
    $doc->toReturn();
    $doc->err(__('Ошибка выбора темы'));
    exit;
}
$id_theme = (int) $_GET['id_theme'];

$q = $db->prepare("SELECT * FROM `forum_themes` WHERE `id` = ? AND `group_edit` <= ?");
$q->execute(Array($id_theme, $user->group));
if (!$theme = $q->fetch()) {
    $doc->toReturn(new url('theme.php', array('id', $theme['id'])));
    $doc->err(__('Тема не доступна'));
    exit;
}


if (empty($theme['id_vote'])) {
    $doc->toReturn(new url('theme.php', array('id', $theme['id'])));
    $doc->err(__('Голосование отсутствует'));
    exit;
}

$q = $db->prepare("SELECT * FROM `forum_vote` WHERE `id` = ?");
$q->execute(Array($theme['id_vote']));

if (!$vote_a = $q->fetch()) {
    $doc->toReturn(new url('theme.php', array('id', $theme['id'])));
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
            $doc->toReturn(new url('theme.php', array('id', $theme['id'])));

            if (!empty($_POST['finish'])) {
                $res = $db->prepare("UPDATE `forum_vote` SET `active` = '0' WHERE `id` = ? LIMIT 1");
                $res->execute(Array($vote_a['id']));
                $dcms->log('Форум', 'Закрытие голосования в теме [url=/forum/theme.php?id=' . $theme['id'] . ']' . $theme['name'] . '[/url]');
                $doc->msg(__('Голосование окончено'));
            } elseif (!empty($_POST['clear'])) {
                $res = $db->prepare("UPDATE `forum_vote` SET `active` = '1' WHERE `id` = ? LIMIT 1");
                $res->execute(Array($vote_a['id']));
                $res = $db->prepare("DELETE FROM `forum_vote_votes` WHERE `id_vote` = ?");
                $res->execute(Array($vote_a['id']));
                $dcms->log('Форум', 'Обнуление голосования в теме [url=/forum/theme.php?id=' . $theme['id'] . ']' . $theme['name'] . '[/url]');
                $doc->msg(__('Голосование начато заново'));
            } elseif (!empty($_POST['delete'])) {
                $res = $db->prepare("DELETE FROM `forum_vote`  WHERE `id` = ? LIMIT 1");
                $res->execute(Array($vote_a['id']));
                $res = $db->prepare("DELETE FROM `forum_vote_votes` WHERE `id_vote` = ?");
                $res->execute(Array($vote_a['id']));
                $res = $db->prepare("UPDATE `forum_themes` SET `id_vote` = null WHERE `id` = ? LIMIT 1");
                $res->execute(Array($theme['id']));
                $dcms->log('Форум', 'Удаление голосования в теме [url=/forum/theme.php?id=' . $theme['id'] . ']' . $theme['name'] . '[/url]');
                $doc->msg(__('Голосование успешно удалено'));
            } else {
                $dcms->log('Форум', 'Изменение параметров голосования в теме [url=/forum/theme.php?id=' . $theme['id'] . ']' . $theme['name'] . '[/url]');
                $res = $db->prepare("UPDATE `forum_vote` SET " . implode(', ', $set) . ", `name` = ? WHERE `id` = ? LIMIT 1");
                $res->execute(Array($vote, $vote_a['id']));
                $doc->msg(__('Изменения сохранены'));
            }

            if (isset($_GET['return'])) {
                $doc->opt('В тему', text::toValue($_GET['return']));
            } else {
                $doc->opt(__('В тему'), 'theme.php?id=' . $theme['id']);
            }
            exit;
        }
    }
}

$form = new form();
$form->textarea('vote', __('Вопрос'), $vote_a['name']);
for ($i = 1; $i <= 10; $i++) {
    $form->text("v$i", __('Ответ №') . $i, $vote_a['v' . $i]);
}
$form->checkbox('finish', __('Окончить голосование'));
$form->checkbox('clear', __('Начать заново'));
$form->checkbox('delete', __('Удалить голосование'));
$form->button(__('Сохранить изменения'));
$form->display();

if (isset($_GET['return'])) {
    $doc->opt(__('В тему'), text::toValue($_GET['return']));
} else {
    $doc->opt(__('В тему'), 'theme.php?id=' . $theme['id']);
}
