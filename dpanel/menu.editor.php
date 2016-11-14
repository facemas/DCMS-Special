<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(6);
$groups = groups::load_ini();
$doc->title = __('Редактор меню');

$menus = menus::getAllMenus();

if (isset($_GET['menu_key']) && menus::exists($_GET['menu_key'])) {
    $menu = new menu($_GET['menu_key']);
    $doc->title = __('Меню "%s"', $menu->getMenuKey());
    $items = $menu->getItems(null, true);
    $listing = new listing();
    foreach ($items AS $item) {
        $post = $listing->post();
        $post->title = $item['title'];
        if ($item['url']) {
            $post->content[] = __('Адрес ссылки: %s', $item['url']);
        }
        if ($item['items']) {
            $post->content[] = __('Кол-во дочерних элементов: %s', count($item['items']));
        }
    }
    $listing->display();

    $doc->ret(__('Список меню'), '?');
    $doc->ret(__('Управление'), './?');
    exit;
}

// region Создание меню
if (isset($_GET['act']) && $_GET['act'] === 'create') {
    $doc->title = __('Создание меню');
    if (isset($_POST['create'])) {
        if (empty($_POST['menu_key'])) {
            $doc->err(__('Название меню не задано'));
        } elseif (menus::exists($_POST['menu_key'])) {
            $doc->err(__('Меню с таким названием уже существует'));
        } elseif (empty($_POST['title']) || !is_array($_POST['title']) || !count($_POST['title'])) {
            $doc->err(__('Должен быть указать хоть один пункт меню'));
        } else {
            $menu = new menu();
            foreach ($_POST['title'] AS $index => $title) {
                $title = text::input_text($title);
                if (!$title) {
                    continue;
                }
                $url = !empty($_POST['url'][$index]) ? text::input_text($_POST['url'][$index]) : null;
                $menu->addItem($title, $url);
            }
            if (!$menu->getItems()) {
                $doc->err(__('Не добавлено ни одного пункта'));
            } else {
                try {
                    $menu->save(text::input_text($_POST['menu_key']));
                    $doc->msg(__('Меню успешно создано'));
                } catch (Exception $e) {
                    $doc->err($e->getMessage());
                }
            }
        }

        $doc->toReturn(new url());
        $doc->ret(__('Список меню'), '?');
        $doc->ret(__('Управление'), './?');
        exit;
    }

    $form = new form(new url(null));
    $form->text('menu_key', __('Название меню'));

    for ($i = 0; $i < 6; $i++) {
        $form->bbcode(''); // для переноса
        $form->text('title[]', __('Пункт меню %s (название)', $i + 1));
        $form->text('url[]', __('Пункт меню %s (ссылка)', $i + 1));
    }

    $form->button(__('Создать'), 'create');
    $form->display();

    $doc->ret(__('Список меню'), '?');
    $doc->ret(__('Управление'), './?');
    exit;
}
//endregion

$doc->title = __('Список меню');
$doc->opt(__('Создать меню'), new url(null, array('act' => 'create')));
$listing = new listing();
foreach ($menus AS $menu) {
    $post = $listing->post();
    $post->title = $menu->getMenuKey();
    $post->setUrl(new url(null, array('menu_key' => $menu->getMenuKey())));
}
$listing->display(__('Нет ни одного меню'));

$doc->ret(__('Управление'), './?');
