<?php
if ($news['id_vote']) {
$q = $db->prepare("SELECT * FROM `news_vote` WHERE `id` = ? AND `group_view` <= ?");
$q->execute(Array($news['id_vote'], $user->group));
if ($vote = $q->fetch()) {
$votes = new votes($vote['name']);
$res = $db->prepare("SELECT COUNT(*) FROM `news_vote_votes` WHERE `id_vote` = ? AND `id_user` = ?");
$res->execute(Array($news['id_vote'], $user->id));
$vote_accept = ($res->fetchColumn()) ? false : true;
if (!$vote['active'])
$vote_accept = false;
$q = $db->prepare("SELECT `vote`, COUNT(*) AS `count` FROM `news_vote_votes` WHERE `id_vote` = ? GROUP BY `vote`");
$q->execute(Array($news['id_vote']));
$countets = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0);
while ($r = $q->fetch()) {
$countets[$r['vote']] = $r['count'];
}
for ($i = 1; $i <= 10; $i++) {
if ($vote['v' . $i]) {
$votes->vote($vote['v' . $i], $countets[$i], '?id=' . $news['id'] . '&amp;vote=' . $i);
}
}
if (!empty($_GET['vote']) && $user->group >= $vote['group_vote'] && $vote_accept) {
$vote_add = (int)$_GET['vote'];
if ($vote['v' . $vote_add]) {
$res = $db->prepare("INSERT INTO `news_vote_votes` (`id_vote`, `id_news`, `id_user`, `vote`) VALUES (?,?,?,?)");
$res->execute(Array($vote['id'], $news['id'], $user->id, $vote_add));
$doc->msg(__('Ваш голос успешно засчитан'));
header('Refresh: 1; url=?id=' . $news['id']);
$doc->ret(__('Вернуться в запись'), '?id=' . $news['id']);
exit;
}
}
$votes->display($user->group >= $vote['group_vote'] && $vote_accept);
}
}