<?php

include_once '../sys/inc/start.php';
$groups = groups::load_ini(); // загружаем массив групп
$doc = new document ();
$doc->title = __('Редактирование раздела');

if (!isset($_GET ['id']) || !is_numeric($_GET ['id'])) {
    header('Refresh: 1; url=./');
    $doc->err(__('Ошибка выбора раздела'));
    exit();
}
$id_topic = (int) $_GET ['id'];

$q = $db->prepare("SELECT * FROM `forum_topics` WHERE `id` = ? AND `group_edit` <= ?");
$q->execute(Array($id_topic, $user->group));
if (!$topic = $q->fetch()) {
    header('Refresh: 1; url=./');
    $doc->err(__('Раздел не доступен для редактирования'));
    exit;
}

if (isset($_POST ['save'])) {
    if (isset($_POST ['name'])) {
        $name = text::for_name($_POST ['name']);
        $description = text::input_text($_POST ['description']);
        $keywords = text::input_text($_POST ['keywords']);
        $theme_view = isset($_POST['theme_view']) ? 1 : 0;

        if ($theme_view != $topic['theme_view']) {
            $dcms->log('Форум', 'Изменение отображения тем раздела "' . $topic ['name'] . '" в списке новых и обновленных');
            $topic['theme_view'] = $theme_view;
            $res = $db->prepare("UPDATE `forum_topics` SET `theme_view` = ? WHERE `id` = ? LIMIT 1");
            $res->execute(Array($theme_view, $topic['id']));
            $doc->msg(__('Отображение тем успешно изменено'));
        }

        if ($name && $name != $topic ['name']) {
            $dcms->log('Форум', 'Изменение названия раздела "' . $topic ['name'] . '" на [url=/forum/topic.php?id=' . $topic ['id'] . ']"' . $name . '"[/url]');
            $topic ['name'] = $name;
            $res = $db->prepare("UPDATE `forum_topics` SET `name` = ? WHERE `id` = ? LIMIT 1");
            $res->execute(Array($topic ['name'], $topic['id']));
            $doc->msg(__('Название раздела успешно изменено'));
        }
    }

    if ($description != $topic ['description']) {
        $dcms->log('Форум', 'Изменение описания раздела [url=/forum/topic.php?id=' . $topic ['id'] . ']"' . $topic ['name'] . '"[/url]');
        $topic ['description'] = $description;
        $res = $db->prepare("UPDATE `forum_topics` SET `description` = ? WHERE `id` = ? LIMIT 1");
        $res->execute(Array($topic ['description'], $topic['id']));
        $doc->msg(__('Описание раздела успешно изменено'));
    }

    if ($keywords != $topic ['keywords']) {
        $dcms->log('Форум', 'Изменение ключевых слов раздела [url=/forum/topic.php?id=' . $topic ['id'] . ']"' . $topic ['name'] . '"[/url]');
        $topic ['keywords'] = $keywords;
        $res = $db->prepare("UPDATE `forum_topics` SET `keywords` = ? WHERE `id` = ? LIMIT 1");
        $res->execute(Array($topic ['keywords'], $topic['id']));
        $doc->msg(__('Ключевые слова раздела успешно измененены'));
    }

    if (isset($_POST ['category'])) {
        $category = (int) $_POST ['category'];
        $q = $db->prepare("SELECT * FROM `forum_categories` WHERE `id` = ? AND `group_show` <= ? AND `group_write` <= ?");
        $q->execute(Array($category, $user->group, $user->group));
        if ($category != $topic ['id_category'] AND $category = $q->fetch()) {
            $topic ['id_category'] = $category ['id'];
            $dcms->log('Форум', 'Перемещение раздела [url=/forum/topic.php?id=' . $topic ['id'] . ']' . $topic ['name'] . '[/url] в категорию [url=/forum/category.php?id=' . $category ['id'] . ']' . $category ['name'] . '[/url]');
            $res = $db->prepare("UPDATE `forum_topics` SET `id_category` = ? WHERE `id` = ? LIMIT 1");
            $res->execute(Array($topic['id_category'], $topic['id']));
            $res = $db->prepare("UPDATE `forum_themes` SET `id_category` = ? WHERE `id_topic` = ?");
            $res->execute(Array($topic['id_category'], $topic['id']));
            $res = $db->prepare("UPDATE `forum_messages` SET `id_category` = ? WHERE `id_topic` = ?");
            $res->execute(Array($topic['id_category'], $topic['id']));
            $doc->msg(__('Раздел успешно перемещен'));
        }
    }

    if (isset($_POST ['group_show'])) { // просмотр
        $group_show = (int) $_POST ['group_show'];
        if (isset($groups [$group_show]) && $group_show != $topic ['group_show']) {
            $topic ['group_show'] = $group_show;
            $res = $db->prepare("UPDATE `forum_topics` SET `group_show` = ? WHERE `id` = ? LIMIT 1");
            $res->execute(Array($topic['group_show'], $topic['id']));
            $doc->msg(__('Читать раздел теперь разрешено группе %s и выше', groups::name($group_show)));
            $dcms->log('Форум', 'Изменение прав чтения раздела [url=/forum/topic.php?id=' . $topic ['id'] . ']' . $topic ['name'] . '[/url] для группы ' . groups::name($group_show));
        }
    }

    if (isset($_POST ['group_write'])) { // запись
        $group_write = (int) $_POST ['group_write'];
        if (isset($groups [$group_write]) && $group_write != $topic ['group_write']) {
            if ($topic ['group_show'] > $group_write)
                $doc->err('Для того, чтобы создавать темы группе "' . groups::name($group_write) . '" сначала необходимо дать права на просмотр раздела');
            else {
                $topic ['group_write'] = $group_write;
                $res = $db->prepare("UPDATE `forum_topics` SET `group_write` = ? WHERE `id` = ? LIMIT 1");
                $res->execute(Array($topic['group_write'], $topic['id']));
                $doc->msg(__('Создавать темы в разделе теперь разрешено группе %s и выше', groups::name($group_write)));
                $dcms->log('Форум', 'Изменение прав создания тем в разделе [url=/forum/topic.php?id=' . $topic ['id'] . ']' . $topic ['name'] . '[/url] для группы ' . groups::name($group_write));
            }
        }
    }

    if (isset($_POST ['group_edit'])) { // редактирование
        $group_edit = (int) $_POST ['group_edit'];
        if (isset($groups [$group_edit]) && $group_edit != $topic ['group_edit']) {
            if ($topic ['group_write'] > $group_edit)
                $doc->err('Для изменения параметров раздела группе "' . groups::name($group_edit) . '" сначала необходимо дать права на создание тем');
            else {
                $topic ['group_edit'] = $group_edit;
                $res = $db->prepare("UPDATE `forum_topics` SET `group_edit` = ? WHERE `id` = ? LIMIT 1");
                $res->execute(Array($topic['group_edit'], $topic['id']));
                $doc->msg(__('Изменять параметры раздела теперь разрешено группе %s и выше', groups::name($group_edit)));
                $dcms->log('Форум', 'Изменение прав редактирования раздела [url=/forum/topic.php?id=' . $topic ['id'] . ']' . $topic ['name'] . '[/url] для группы ' . groups::name($group_edit));
            }
        }
    }

    $topic_theme_create_with_wmid = (int) !empty($_POST ['theme_create_with_wmid']);
    if ($topic_theme_create_with_wmid != $topic ['theme_create_with_wmid']) {
        $topic ['theme_create_with_wmid'] = $topic_theme_create_with_wmid;
        $res = $db->prepare("UPDATE `forum_topics` SET `theme_create_with_wmid` = ? WHERE `id` = ? LIMIT 1");
        $res->execute(Array($topic['theme_create_with_wmid'], $topic['id']));
        if ($topic ['theme_create_with_wmid']) {
            $doc->msg(__('Создавать темы в данном разделе теперь смогут только пользователи с активированным WMID'));
        } else {
            $doc->msg(__('Ограничение на создание тем без WMID снято'));
        }

        $dcms->log('Форум', 'Изменение ограничений WMID раздела [url=/forum/topic.php?id=' . $topic ['id'] . ']' . $topic ['name'] . '[/url]');
    }
}

$doc->title = __('Редактирование раздела "%s"', $topic ['name']); // шапка страницы

$form = new form(new url());
$form->text('name', __('Название'), $topic['name']);
$form->textarea('description', __('Описание'), $topic['description']);
$form->text('keywords', __('Ключевые слова'), $topic['keywords']);

$options = array();
$q = $db->prepare("SELECT `id`,`name` FROM `forum_categories` WHERE `group_show` <= ? ORDER BY `position` ASC");
$q->execute(Array($user->group));
while ($category = $q->fetch()) {
    $options [] = array($category ['id'], $category ['name'], $category ['id'] == $topic ['id_category']);
}
$form->select('category', __('Категория'), $options, false);

$options = array();
foreach ($groups as $type => $value) {
    $options [] = array($type, $value ['name'], $type == $topic ['group_show']);
}
$form->select('group_show', __('Просмотр тем'), $options, false);

$options = array();
foreach ($groups as $type => $value) {
    $options [] = array($type, $value ['name'], $type == $topic ['group_write']);
}

$form->select('group_write', __('Создание тем'), $options, false);

$options = array();
foreach ($groups as $type => $value) {
    $options [] = array($type, $value ['name'], $type == $topic ['group_edit']);
}
$form->select('group_edit', __('Изменение параметров'), $options, false);

$form->checkbox('theme_view', __('Отображать темы в списке новых и обновленыых тем'), $topic['theme_view']);
$form->block('<div class="ui mini info message">' . __('Будьте внимательнее при установке доступа выше своего.') . '</div>');
$form->button(__('Сохранить'), 'save');
$form->display();

$doc->opt(__('Удаление тем'), 'topic.themes.delete.php?id=' . $topic ['id']);
$doc->opt(__('Удалить раздел'), 'topic.delete.php?id=' . $topic ['id']);

if (isset($_GET ['return'])) {
    $doc->ret(__('В раздел'), text::toValue($_GET ['return']));
} else {
    $doc->ret(__('В раздел'), 'topic.php?id=' . $topic ['id']);
}

$doc->ret(__('В категорию'), 'category.php?id=' . $topic ['id_category']);
$doc->ret(__('Форум'), './');
