<?php
include_once '../sys/inc/start.php';
dpanel::check_access();
$groups = groups::load_ini();
$doc = new document(4);
$doc->title = __('Изменение статуса');

if (isset($_GET['id_ank'])) $ank = new user($_GET['id_ank']);
else $ank = $user;

if (!$ank->group) {
    $doc->toReturn();
    $doc->err(__('Нет данных'));
    exit;
}

$doc->title .= ' "' . $ank->login . '"';

if ($ank->group >= $user->group) {
    $doc->toReturn();
    $doc->err(__('Ваш статус не позволяет производить действия с данным пользователем'));
    exit;
}

if (isset($_POST['save']) && !empty($_POST['group'])) {
    $group_now = (int) $_POST['group'];
    if ($group_now >= $user->group)
            $doc->err(__('Вы не можете дать пользователю статус эквивалентный своему или выше'));
    else {
        $group_last = $ank->group;
        if ($group_last != $group_now) {
            $res = $db->prepare("INSERT INTO `log_of_user_status` (`id_user`, `id_adm`, `time`, `type_last`, `type_now`) VALUES (?, ?, ?, ?, ?)");
            $res->execute(Array($ank->id, $user->id, TIME, $group_last, $group_now));
            $ank->group = $group_now;

            $dcms->log('Пользователи',
                'Изменение группы пользователя [url=/profile.view.php?id=' . $ank->id . ']' . $ank->login . '[/url] с ' . groups::name($group_last) . ' на ' . groups::name($ank->group));

            $doc->msg(__('Пользователь "%s" теперь "%s"', $ank->login, groups::name($ank->group)));
        }
    }
}

$form = new form(new url());
$options = array();
foreach ($groups as $group => $value) {
    if ($group && $user->group > $group) $options[] = array($group, __($value['name']), $group == $ank->group);
}
$form->select('group', __('Статус'), $options);
$form->button(__('Применить'), 'save');
$form->display();

$doc->ret(__('Действия'), 'user.actions.php?id=' . $ank->id);
$doc->ret(__('Анкета "%s"', $ank->login), '/profile.view.php?id=' . $ank->id);
$doc->ret(__('Админка'), '/dpanel/');
