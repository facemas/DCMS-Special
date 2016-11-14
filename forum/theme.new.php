<?php

include_once '../sys/inc/start.php';
$doc = new document(1);

$doc->title = __('Новая тема');

if (!isset($_GET ['id_topic']) || !is_numeric($_GET ['id_topic'])) {
    $doc->toReturn();
    $doc->err(__('Ошибка выбора раздела'));
    exit();
}
$id_topic = (int) $_GET ['id_topic'];


$q = $db->prepare("SELECT * FROM `forum_topics` WHERE `id` = ? AND `group_write` <= ?");
$q->execute(Array($id_topic, $user->group));
if (!$topic = $q->fetch()) {
    $doc->toReturn();
    $doc->err(__('В выбранном разделе нельзя создавать темы'));
    exit();
}


// лимит на создание тем
$timelimit = (empty($_SESSION ['antiflood'] ['newtheme']) || $_SESSION ['antiflood'] ['newtheme'] < TIME - 3600) ? true : false;



$time_reg = true;
if (!$user->is_writeable) {
    $doc->msg(__('Создавать темы запрещено'), 'write_denied');
    $time_reg = false;
}


if ($user->group >= 2) {
    $timelimit = true; // админ-составу разрешается создавать темы без ограничений по времени
}

if (!$timelimit) {
    $doc->err(__("Разрешается создавать темы не чаще одного раза в час"));
}


$can_write = $timelimit && $time_reg;

if ($can_write && isset($_POST['message']) && isset($_POST ['name'])) {
    $name = text::for_name($_POST['name']);
    $message = text::input_text($_POST['message']);
    $keywords = text::input_text($_POST['keywords']);

    if ($dcms->censure && $mat = is_valid::mat($message))
        $doc->err(__('Обнаружен мат: %s', $mat));
    elseif ($dcms->censure && $mat = is_valid::mat($name))
        $doc->err(__('Обнаружен мат: %s', $mat));
    elseif ($dcms->forum_theme_captcha && $user->group < 2 && (empty($_POST ['captcha']) || empty($_POST ['captcha_session']) || !captcha::check($_POST ['captcha'], $_POST ['captcha_session']))) {
        $doc->err(__('Проверочное число введено неверно'));
    } elseif ($message && $name) {
        $user->balls += $dcms->add_balls_create_theme;
        $res = $db->prepare("UPDATE `forum_topics` SET `time_last` = ? WHERE `id` = ? LIMIT 1");
        $res->execute(Array(TIME, $id_topic));
        $res = $db->prepare("INSERT INTO `forum_themes` (`id_category`, `id_topic`, `keywords`, `name`, `id_autor`, `time_create`, `id_last`, `time_last`, `group_show`, `group_write`, `group_edit`) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $res->execute(Array($topic['id_category'], $topic['id'], $keywords, $name, $user->id, TIME, $user->id, TIME, $topic['group_show'], $topic['group_write'], max($user->group, 2)));
        $theme ['id'] = $db->lastInsertId();
        $res = $db->prepare("SELECT * FROM `forum_themes` WHERE `id` = ? LIMIT 1");
        $res->execute(Array($theme['id']));
        $theme = $res->fetch();

        $res = $db->prepare("INSERT INTO `forum_messages` (`id_category`, `id_topic`, `id_theme`, `id_user`, `time`, `message`, `group_show`, `group_edit`) VALUES (?,?,?,?,?,?,?,?)");
        $res->execute(Array($theme['id_category'], $theme['id_topic'], $theme['id'], $user->id, TIME, $message, $theme['group_show'], $theme['group_edit']));

        $_SESSION ['antiflood'] ['newtheme'] = TIME;
        $doc->msg(__('Тема успешно создана'));

        header('Refresh: 1; url=theme.php?id=' . $theme ['id']);
        $doc->ret(__('В тему'), 'theme.php?id=' . $theme ['id']);
        exit();
    } else {
        $doc->err(__('Сообщение или название темы пусто'));
    }
}

$doc->title = $topic['name'] . ' - ' . __('Новая тема');

if ($can_write) {
    $form = new form(new url());
    $form->text('name', __('Название темы'));
    //$form->bbcode('* ' . __('Название темы должно быть информативным, четко выделяя ее среди других тем. [b]Названия вида "помогите", "как сделать" и т.д. строго запрещены.[/b]'));
    $form->textarea('message', __('Сообщение'));
    $form->text('keywords', __('Ключевые слова'));
    if ($dcms->forum_theme_captcha && $user->group < 2) {
        $form->captcha();
    }
    $form->button(__('Создать тему'));
    $form->display();
}

if (isset($_GET ['return'])) {
    $doc->ret(__('В раздел'), text::toValue($_GET ['return']));
} else {
    $doc->ret(__('В раздел'), 'theme.php?id=' . $theme ['id']);
}
