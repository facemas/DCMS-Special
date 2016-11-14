<?php
include_once '../sys/inc/start.php';
$doc = new document(4);
if (!isset($_GET ['id']) || !is_numeric($_GET ['id'])) {
if (isset($_GET ['return']))
header('Refresh: 1; url=' . $_GET ['return']);
else
header('Refresh: 1; url=./');
$doc->err(__('Новость не выбрана'));
exit();
}
$id_news = (int) $_GET ['id'];
$q = $db->prepare("SELECT * FROM `news` WHERE `id` = ?");
$q->execute(Array($id_news));
if (!$news = $q->fetch()) {
if (isset($_GET ['return']))
header('Refresh: 1; url=' . $_GET ['return']);
else
header('Refresh: 1; url=./');
$doc->err(__('Новости не существует'));
exit;
}
$doc->title = __($news['title'].' : Голосование');
if ($user->group >= 4) {
if (!empty($news['id_vote'])) {
$doc->toReturn(new url('/news/comments.php?id='.$news['id']));
$doc->err(__('Голосование уже создано'));
exit;
}
if (!empty($_POST['vote'])) {
$vote = text::input_text($_POST['vote']);
if (!$vote) $doc->err(__('Заполните поле "Вопрос"'));
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
if (count($v) < 2) $doc->err(__('Должно быть не менее 2-х вариантов ответа'));
else {
$res = $db->prepare("INSERT INTO `news_vote` (`id_user`, `id_news`, `name`, " . implode(', ', $k) . ")
VALUES (?,?,?, " . implode(', ', $v) . ")");
$res->execute(Array($user->id, $news['id'], $vote));
if (!$id_vote = $db->lastInsertId()) $doc->err(__('При создании голосования возникла ошибка'));
else {
$doc->toReturn(new url('/news/comments.php?id='.$news['id']));
$res = $db->prepare("UPDATE `news` SET `id_vote` = ? WHERE `id` = ? LIMIT 1");
$res->execute(Array($id_vote, $news['id']));
$doc->msg('Голосование успешно создано');
$dcms->log('Новость',
'Создание голосования в новости [url=/news/comments.php?id=' . $news['id'] . ']' . $news['title'] . '[/url]');
if (isset($_GET['return'])) $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
else $doc->ret(__('Вернуться'), '/news/comments.php?id=' . $news['id']);
exit;
}
}
}
}
$form = new form(new url());
$form->textarea('vote', __('Вопрос'));
for ($i = 1; $i <= 10; $i++)
$form->text("v$i", __('Ответ №') . $i);
$form->button(__('Создать голосование'));
$form->display();
}else{
$doc->err(__('Доступ ограничен'));
}
if (isset($_GET['return'])) $doc->ret(__('Вернуться'), text::toValue($_GET['return']));
else $doc->ret(__('Вернуться'), '/news/comments.php?id=' . $news['id']);