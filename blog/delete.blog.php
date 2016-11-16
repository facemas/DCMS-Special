<?php

include_once '../sys/inc/start.php';
$doc = new document(1);
$doc->title = __('Удаление');
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
$autor = new user((int) $blogs['autor']);
if ($autor->group > $user->group)
    $doc->access_denied(__('Недостаточно прав для удаления данной записи'));
if ($autor->id == $user->id || $user->group >= 2) {
    if (isset($_POST['delete'])) {

        $q = $db->prepare("DELETE FROM `blog` WHERE `id` = ? LIMIT 1");
        $q->execute(Array($id_blog));
        $q = $db->prepare("DELETE FROM `blog_comment` WHERE `id` = ?");
        $q->execute(Array($id_blog));
        $q = $db->prepare("DELETE FROM `blog_view` WHERE `id` = ?");
        $q->execute(Array($id_blog));
        $dir = new files(FILES . '/.blog/' . $id_blog);
        $dir->delete();
        unset($dir);
        $doc->msg(__('Запись удалена'));
        header('Refresh: 1; url=./');
        exit;
    }
    $smarty = new design();
    $smarty->assign('method', 'post');
    $smarty->assign('action', '?id=' . $id_blog . '&amp;' . passgen());
    $elements = array();
    $elements[] = array('type' => 'text', 'value' => '* ' . __('Запись "%s" будет удалена', $blogs['name']), 'br' => 1);
    $elements[] = array('type' => 'submit', 'br' => 0, 'info' => array('name' => 'delete', 'value' => __('Удалить')));
    $smarty->assign('el', $elements);
    $smarty->display('input.form.tpl');
} else {
    $doc->err(__('Доступ запрещен'));
}
$doc->ret(__('К записи'), 'blog.php?blog=' . $id_blog . '');
$doc->ret(__('Блоги'), 'index.php');
?>