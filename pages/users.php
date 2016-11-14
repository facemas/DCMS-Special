<?php

include_once '../sys/inc/start.php';
$doc = new document();

$doc->title = __('Пользователи');
$none = __('Нет пользователей');

switch ($order = (string) @$_GET['order']) {
    case 'birthdays':
        $sort = 'DESC';
        $order = 'ank_g_r';
        $where = "WHERE `ank_m_r` = '" . date("m") . "' AND `ank_d_r` = '" . date("d") . "'";
        $none = __('Сегодня нет именинников');
        $doc->title = __('Именинники');
        break;
    case 'age':
        $order = "ank_g_r";
        $sort = 'DESC,`ank_m_r` DESC, `ank_d_r` DESC';
        $none = __('Нет пользователей, указавших дату рождения');
        $where = "WHERE `ank_g_r` > '0' AND `ank_m_r` > '0' AND `ank_d_r` > '0'";
        break;
    case 'group':
        $sort = 'DESC';
        $where = "WHERE `group` > '1'";
        $doc->title = __('Администрация');
        break;
    case 'login':
        $order = 'login';
        $sort = 'ASC';
        $where = '';
        break;
    case 'balls':
        $order = 'balls';
        $sort = 'DESC';
        $where = '';
        break;
    case 'rating':
        $order = 'rating';
        $sort = 'DESC';
        $where = '';
        break;
    default:
        $order = 'id';
        $sort = 'DESC';
        $where = '';
        break;
}

if (!empty($_GET['search'])) {
    $search = text::input_text($_GET['search']);
}
if (isset($search) && !$search) {
    $doc->err(__('Пустой запрос'));
} elseif (isset($search) && $search) {
    $where = "WHERE `login` LIKE " . $db->quote('%' . $search . '%');
    $doc->title = __('Поиск по запросу "%s"', $search);
}

$posts = array();
$pages = new pages;

$res = $db->query("SELECT COUNT(*) FROM `users` $where");
$pages->posts = $res->fetchColumn();

if ((string) @$_GET['order'] != 'birthdays') {
    /* проверяем есть-ли сегодня именинники */
    $res = $db->query("SELECT COUNT(*) FROM `users` WHERE `ank_m_r` = '" . date("m") . "' AND `ank_d_r` = '" . date("d") . "'");
    $bds = $res->fetchColumn();
} else {
    $bds = $pages->posts;
}

// меню сортировки
$ord = array();
$ord[] = array("?order=id&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('ID пользователя'), $order == 'id');
if ($bds || (string) @$_GET['order'] == 'birthdays') {
    $ord[] = array("?order=birthdays&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('Именинники'), (string) @$_GET['order'] == 'birthdays');
}
$ord[] = array("?order=rating&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('Рейтинг'), $order == 'rating');
$ord[] = array("?order=balls&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('Баллы'), $order == 'balls');
$ord[] = array("?order=group&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('Статус'), $order == 'group');
$ord[] = array("?order=login&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('Логин'), $order == 'login');
$ord[] = array("?order=age&amp;page={$pages->this_page}" . (isset($search) ? '&amp;search=' . urlencode($search) : ''), __('Возраст'), (string) @$_GET['order'] == 'age');

$or = new design();
$or->assign('order', $ord);
$or->display('design.order.tpl');

$q = $db->query("SELECT `id` FROM `users` $where ORDER BY `$order` " . $sort . " LIMIT $pages->limit");

$listing = new listing();
if ($arr = $q->fetchAll()) {
    foreach ($arr AS $ank) {
        $post = $listing->post();
        $p_user = new user($ank['id']);

        $post->image = $p_user->getAvatar();
        $post->title = $p_user->nick();
        $post->url = '/profile.view.php?id=' . $p_user->id;

        switch ($order) {
            case 'id':
                $post->content[] = '[b]' . 'ID: ' . $p_user->id . '[/b]';
                break;
            case 'group':
                $post->content[] = '[b]' . $p_user->group_name . '[/b]';
                break;
            case 'rating':
                $post->content[] = '[b]' . __('Рейтинг') . ': ' . $p_user->rating . '[/b]';
                break;
            case 'balls':
                $post->content[] = '[b]' . __('Баллы') . ': ' . ((int) $p_user->balls) . '[/b]';
                break;
            case 'ank_g_r':
                $post->content[] = '[b]' . (((int) $p_user->ank_g_r > 0) ? __('Дата рождения') . ": $p_user->ank_d_r.$p_user->ank_m_r.$p_user->ank_g_r (" . misc::get_age($p_user->ank_g_r, $p_user->ank_m_r, $p_user->ank_d_r) . ')' : __('День рождения') . ": $p_user->ank_d_r " . misc::getLocaleMonth($p_user->ank_m_r)) . '[/b]';
                break;
        }

        //$post->content[] = '[small]' . __('Дата регистрации') . ': ' . date('d-m-Y', $p_user->reg_date) . '[/small]';
        //$post->content[] = '[small]' . __('Последний визит') . ': ' . ($p_user->last_visit ? misc::when($p_user->last_visit) : misc::when($p_user->reg_date)) . '[/small]';
    }

    if ($order == 'ank_g_r') { /* fix */
        $order = (string) $_GET['order'];
    }
}

$form = new form('?', false);
$form->hidden('order', $order);
$form->text('search', __('Логин или его часть'), @$search, false);
$form->button(__('Поиск'));
$form->display();

$listing->display($none);

$pages->display("?order=$order&amp;" . (isset($search) ? 'search=' . urlencode($search) . '&amp;' : ''));
