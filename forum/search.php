<?php

include_once '../sys/inc/start.php';
$doc = new document();

// результаты
$searched = & $_SESSION['search']['result'];
// маркеры (выделение найденых слов)
$searched_mark = & $_SESSION['search']['mark'];
// запрос
$search_query = & $_SESSION['search']['query'];
// запрос (массив для mysql)
$search_query_sql = & $_SESSION['search']['query_sql'];
$doc->title = __('Поиск');

if ($dcms->forum_search_reg && !$user->group) {
    $doc->err(__('Поиск по форуму доступен только зарегистрированым пользователям'));
    $doc->ret(__('К категориям'), './');
    exit;
}

if (!isset($_GET['cache']) || empty($searched)) {
    $searched = array();
    $search_query = null;
    $search_query_sql = array();
    $searched_mark = array();
}
if (!empty($_POST['query'])) {
    if ($dcms->forum_search_captcha && (empty($_POST['captcha']) || empty($_POST['captcha_session']) || !captcha::check($_POST['captcha'], $_POST['captcha_session']))) {
        $doc->err(__('Код с картинки введен неверно'));
    } else {
        $stemmer = new stemmer();
        $searched = array();
        $search_query = text::input_text($_POST['query']);
        $search_query_sql = array();
        $searched_mark = array();
        // массив всех слов
        $search_array = preg_split('#\s+#u', text::input_text($_POST['query']));
        // текст запроса без лишних пробелов
        $search_query = implode(' ', $search_array);
        for ($i = 0; $i < count($search_array); $i++) {
            $st = $stemmer->stem_word($search_array[$i]);
            // пропускаем слова, состоящие менее чем из 3-х символов
            if (text::strlen($st) < 3)
                continue;
            // составляем регулярки для подсведки найденных слов
            $searched_mark[$i] = '#([^\[].*)(' . preg_quote($st, '#') . '[a-zа-я0-9]*)([^\]].*)#ui';
            $search_query_sql[$i] = '+' . $st . '*';
        }
        $q = $db->prepare("SELECT `forum_themes`.`id`,`forum_themes`.`name`, `forum_messages`.`message`, `forum_messages`.`id` AS `id_message` FROM `forum_themes` LEFT JOIN `forum_messages` ON `forum_themes`.`id` = `forum_messages`.`id_theme` WHERE `forum_themes`.`group_show` <= ? AND  (`forum_messages`.`group_show` IS NULL OR `forum_messages`.`group_show` <= ?) AND MATCH (`forum_themes`.`name`,`forum_messages`.`message`) AGAINST (" . $db->quote(implode(' ', $search_query_sql)) . " IN BOOLEAN MODE) GROUP BY `forum_themes`.`id`");
        $q->execute(Array($user->group, $user->group));
        $searched = $q->fetchAll();
    }
}

$listing = new listing();
$pages = new pages;
$pages->posts = count($searched);
// конец цикла
$end = min($pages->items_per_page * $pages->this_page, $pages->posts);
$start = $pages->my_start();
for ($i = $start; $i < $end; $i++) {
    $post = $listing->post();

    $theme = $searched[$i];
    $title = preg_replace($searched_mark, '\1<span class="mark">\2</span>\3', text::toValue($theme['name']));
    $post->content = text::toOutput(preg_replace($searched_mark, '\1[mark]\2[/mark]\3', $theme['message']));

    $post->title = $title;
    $post->url = 'theme.php?id=' . $theme['id'];

    if ($post->content) {
        $post->content .= "<br /><a href='message.php?id_message=$theme[id_message]&amp;return=" . urlencode('search.php?cache&page=' . $pages->this_page) . "'>" . __('К сообщению') . "</a>";
    }
}

$listing->display($search_query ? __('Результаты по запросу "%s" отсутствуют', $search_query) : false);

$pages->display('?cache&amp;'); // вывод страниц

$form = new form('?' . passgen());
$form->text('query', __('Что ищем'), $search_query, $dcms->forum_search_captcha);
if ($dcms->forum_search_captcha) {
    $form->captcha();
}
$form->button(__('Поиск'));
$form->display();

$doc->ret(__('Форум'), './');
