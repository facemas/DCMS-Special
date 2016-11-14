<?php
include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(2);
$doc->title = __('Бан пользователя');

$ank = new user(@$_GET ['id_ank']);

if (!$ank->group) {
    $doc->toReturn();
    $doc->err(__('Нет данных о пользователе'));
    exit;
}

if ($ank->group >= $user->group) {
    $doc->toReturn();
    $doc->err(__('Недостаточно привилегий'));
    exit;
}

$doc->title = __('Бан "%s"', $ank->login);

$link = !empty($_GET ['link']) ? $_GET ['link'] : (!empty($_POST ['link']) ? $_POST ['link'] : false);
$code = !empty($_GET ['code']) ? $_GET ['code'] : (!empty($_POST ['code']) ? $_POST ['code'] : false);

$codes = new menu_code('code');

if (!$code && !isset($_GET ['skip'])) {

    $form = new form(new url());
    $form->text('link', __('Ссылка'), $link);
    if ($link) $form->bbcode('[url="' . $link . '"]' . __('Перейти к нарушению') . '[/url]');
    $form->select('code', __('Нарушение'), $codes->options());
    $form->button(__('Далее'));
    $form->display();

    if (isset($_GET ['return'])) {
        $doc->ret(__('Вернуться'), text::toValue($_GET ['return']));
    }
    $doc->ret(__('В анкету'), '/profile.view.php?id=' . $ank->id);
    $doc->ret(__('Админка'), './');
    exit;
}
// получение минимального и максимального срока банов
list ( $min, $max ) = $codes->get_time($code);

if (isset($_POST ['ban'])) {
    $comm = text::input_text(@$_POST ['comment']);
    $access_view = (int) empty($_POST ['full']);

    $time_1 = abs((int) @$_POST ['time']);
    switch (@$_POST ['timem']) {
        case 'forever':
            $time_ban_end = null;
            break;
        case 'm' :
            $time_ban_end = $time_1 * 60 + TIME;
            break;
        case 'h' :
            $time_ban_end = $time_1 * 3600 + TIME;
            break;
        case 'd' :
            $time_ban_end = $time_1 * 86400 + TIME;
            break;
        case 'ms' :
            $time_ban_end = $time_1 * 2592000 + TIME;
            break;
    }

    $res = $db->prepare("SELECT COUNT(*) FROM `ban` WHERE `id_user` = ? AND `link` = ? AND `code` = ?");
    $res->execute(Array($ank->id, $link, $code));
    if (!$time_1) {
        $doc->err(__('Не корректное время бана'));
    } elseif (!isset($codes->menu_arr [$code])) {
        $doc->err(__('Не выбрано нарушение'));
    } elseif (!$comm) {
        $doc->err(__('Необходимо прокомментировать бан'));
    } elseif ($link && $res->fetchColumn()) {
        $doc->err(__('Нельзя банить пользователя несколько раз за одно и то же нарушение'));
    } else {
        // делаем все жалобы обработанными
        $res = $db->prepare("UPDATE `complaints` SET `processed` = '1' WHERE `id_ank` = ? AND `link` = ? AND `code` = ?");
        $res->execute(Array($ank->id, $link, $code));
        // ставим запись о бане
        $res = $db->prepare("INSERT INTO `ban` (`id_user`, `id_adm`, `link`, `code`, `comment`, `time_start`, `time_end`, `access_view`)
VALUES (?, ?, ?, ?, ?, ?, ?, ?) ");
        $res->execute(Array($ank->id, $user->id, $link, $code, $comm, TIME, $time_ban_end, $access_view));
        $res = $db->prepare("UPDATE `users` SET `is_ban` = '1' WHERE `id` = ?");
        $res->execute(Array($ank->id));

        $dcms->log('Пользователи',
            'Бан пользователя [user]' . $ank->id . '[/user] на' . ($time_ban_end == 'NULL' ? 'всегда' : (' ' . misc::when($time_ban_end) )) . "\nПричина: $comm");

        if ($time_ban_end == 'NULL') {
            $doc->msg(__('Пользователь успешно забанен навсегда'));
        } else {
            $doc->msg(__('Пользователь успешно забанен на %s', misc::when($time_ban_end)));
        }
    }
}

if (!empty($_GET['ban_delete'])) {
    $id_ban_delete = (int) $_GET['ban_delete'];
    $q = $db->prepare("SELECT * FROM `ban` WHERE `id_user` = ? AND `id` = ?");
    $q->execute(Array($ank->id, $id_ban_delete));
    if (!$ban = $q->fetch()) {
        $doc->err(__('Ошибка снятия бана'));
    } else {
        $adm = new user($ban['id_adm']);
        if ($adm->group < $user->group || $adm->id == $user->id) {

            $res = $db->prepare("DELETE FROM `ban` WHERE `id` = ? LIMIT 1");
            $res->execute(Array($id_ban_delete));

            $doc->msg(__('Бан успешно снят'));
        } else {
            $doc->err(__('Недостаточно привилегий'));
        }
    }
}

$listing = new listing();
// список нарушений

$q = $db->prepare("SELECT * FROM `ban` WHERE `id_user` = ? ORDER BY `id` DESC");
$q->execute(Array($ank->id));
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $c) {
        $post = $listing->post();
        $adm = new user($c ['id_adm']);
        $post->action('delete', '?id_ank=' . $ank->id . '&amp;ban_delete=' . $c['id'] . '&amp;skip');
        $post->title = $adm->nick();
        $post->time = misc::when($c ['time_start']);
        $post->content[] = __('Нарушение: %s', $c['code']);

        if ($c ['time_start'] && TIME < $c ['time_start'])
                $post->content[] = '[b]' . __('Начало действия') . ':[/b]' . misc::when($c ['time_start']);

        if ($c['time_end'] === NULL) $post->content[] = '[b]' . __('Пожизненная блокировка') . "[/b]";
        elseif (TIME < $c['time_end']) $post->content[] = __('Осталось: %s', misc::when($c['time_end']));

        if ($c['link']) $post->content[] = __('Ссылка на нарушение: %s', $c['link']);
        $post->content[] = __('Комментарий: %s', $c['comment']);
    }
}

$listing->display(__('Нарушения отсутствуют'));
$form = new form(new url());
$form->text('link', __('Ссылка'), $link);
if ($link) $form->bbcode('[url="' . $link . '"]' . __('Перейти к нарушению') . '[/url]');
$form->select('code', __('Нарушение'), $codes->options($code));

if (!$min || $min < 3600) {
    $time = max(round($min / 60), 10);
    $timem = 'm';
} elseif ($min < 86400) {
    $time = max(round($min / 3600), 1);
    $timem = 'h';
} elseif ($min < 2592000) {
    $time = max(round($min / 86400), 1);
    $timem = 'd';
} else {
    $time = max(round($min / 2592000), 1);
    $timem = 'ms';
}

$form->text('time', __('Срок бана'), $time, false, 3);
$options = array();
$options[] = array('m', __('Минуты'), $timem == 'm');
$options[] = array('h', __('Часы'), $timem == 'h');
$options[] = array('d', __('Сутки'), $timem == 'd');
$options[] = array('ms', __('Месяцы'), $timem == 'ms');
$options[] = array('forever', __('Навсегда'));
$form->select('timem', false, $options);
$form->checkbox('full', __('Полная блокировка') . ' *');
$form->bbcode('* ' . __('Блокирует всю навигацию на сайте'));
$form->textarea('comment', __('Комментарий'));
$form->button(__('Забанить'), 'ban');
$form->display();


if (isset($_GET ['return'])) {
    $doc->ret(__('Вернуться'), text::toValue($_GET ['return']));
}

$doc->ret(__('В анкету'), '/profile.view.php?id=' . $ank->id);
$doc->ret(__('Админка'), './');
