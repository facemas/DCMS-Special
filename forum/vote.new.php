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
    $doc->toReturn(new url('theme.php', array('id', $id_theme)));
    $doc->err(__('Тема не доступна'));
    exit;
}


if (!empty($theme['id_vote'])) {
    $doc->toReturn(new url('theme.php', array('id', $id_theme)));
    $doc->err(__('Голосование уже создано'));
    exit;
}

if (!empty($_POST['vote'])) {
    $vote = text::input_text($_POST['vote']);
    if (!$vote)
        $doc->err(__('Заполните поле "Вопрос"'));

    else {
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
            $res = $db->prepare("INSERT INTO `forum_vote` (`id_autor`, `id_theme`, `name`, " . implode(', ', $k) . ") VALUES (?,?,?, " . implode(', ', $v) . ")");
            $res->execute(Array($user->id, $theme['id'], $vote));

            if (!$id_vote = $db->lastInsertId()) {
                $doc->err(__('При создании голосования возникла ошибка'));
            } else {
                $doc->toReturn(new url('theme.php', array('id', $id_theme)));
                $res = $db->prepare("UPDATE `forum_themes` SET `id_vote` = ? WHERE `id` = ? LIMIT 1");
                $res->execute(Array($id_vote, $theme['id']));
                $doc->msg('Голосование успешно создано');

                $dcms->log('Форум', 'Создание голосования в теме [url=/forum/theme.php?id=' . $theme['id'] . ']' . $theme['name'] . '[/url]');

                if (isset($_GET['return'])) {
                    $doc->ret(__('В тему'), text::toValue($_GET['return']));
                } else {
                    $doc->ret(__('В тему'), 'theme.php?id=' . $theme['id']);
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

if (isset($_GET['return'])) {
    $doc->ret(__('В тему'), text::toValue($_GET['return']));
} else {
    $doc->ret(__('В тему'), 'theme.php?id=' . $theme['id']);
}
