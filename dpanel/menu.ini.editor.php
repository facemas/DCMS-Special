<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(6);
$groups = groups::load_ini();
$doc->title = __('Редактор меню');

$menu_files = (array) glob(H . '/sys/ini/menu.*.ini');
$menus = array();
foreach ($menu_files as $menu_path) {
    if (!preg_match('#menu\.(.+?)\.ini$#ui', $menu_path, $m)) {
        continue;
    }
    $menus[] = $m[1];
}

if (!empty($_GET['menu'])) {
    $menu = (string) $_GET['menu'];

    if (!in_array($menu, $menus)) {
        $doc->err(__('Запрошенное меню не найдено'));
        exit;
    }
    $doc->title = __('Меню "%s" - редактирование', $menu);
    $m_obj = new menu_ini($menu);

    if (!empty($_GET['item'])) {
        $item_name = (string) $_GET['item'];
        if (!isset($m_obj->menu_arr[$item_name])) {
            $doc->err(__('Ошибка при выборе пункта меню'));
        }

        $item = $m_obj->menu_arr[$item_name];
        $doc->title = __('Меню "%s" - %s', $menu, $item_name);
        if (!empty($_POST['delete'])) {
            if (empty($_POST['captcha']) || empty($_POST['captcha_session']) || !captcha::check($_POST['captcha'], $_POST['captcha_session'])) {
                $doc->err(__('Проверочное число введено неверно'));
            } else {
                $ini = ini::read(H . '/sys/ini/menu.' . $menu . '.ini', true);
                unset($ini[$item_name]);
                if (ini::save(H . '/sys/ini/menu.' . $menu . '.ini', $ini, true)) {
                    $doc->msg(__('Пункт меню успешно удален'));
                } else {
                    $doc->err(__('Ошибка при сохранении файла'));
                }
                header('Refresh: 1; url=?menu=' . urlencode($menu) . '&amp;' . passgen());
                $doc->ret(__('Меню "%s"', $menu), '?menu=' . urlencode($menu) . '&amp;' . passgen());
                $doc->ret(__('Список меню'), '?' . passgen());
                $doc->ret(__('Управление'), './?' . passgen());
                exit;
            }
        }
        if (!empty($_POST['save'])) {
            $name = text::input_text(@$_POST['name']);
            $url = text::input_text(@$_POST['url']);
            $icon = text::input_text(@$_POST['icon']);
            $counter = text::input_text(@$_POST['counter']);
            $razdel = (int) !empty($_POST['razdel']);
            $is_vip = (int) !empty($_POST['is_vip']);
            $group = (int) @$_POST['group'];
            $position = (int) @$_POST['position'];
            
            if (empty($name)) {
                $doc->err(__('Название не может быть пустым'));
            } elseif ($name != $item_name && isset($m_obj->menu_arr[$name])) {
                $doc->err(__('Выбранное название меню уже занято'));
            } else {
                $ini = ini::read(H . '/sys/ini/menu.' . $menu . '.ini', true);
                if ($name != $item_name) {
                    unset($ini[$item_name]);
                }

                $ini[$name] = array('url' => $url,
                    'icon' => $icon,
                    'counter' => $counter,
                    'razdel' => $razdel,
                    'is_vip' => $is_vip,
                    'group' => $group
                );
                
                arraypos::setPosition($ini, $name, $position);
                
                if (ini::save(H . '/sys/ini/menu.' . $menu . '.ini', $ini, true)) {
                    $doc->msg(__('Изменения сохранены'));
                } else {
                    $doc->err(__('Ошибка при сохранении файла'));
                }
                header('Refresh: 1; url=?menu=' . urlencode($menu) . '&amp;' . passgen());
                $doc->ret(__('Меню "%s"', $menu), '?menu=' . urlencode($menu) . '&amp;' . passgen());
                $doc->ret(__('Список меню'), '?' . passgen());
                $doc->ret(__('Управление'), './?' . passgen());
                exit;
            }
        }



        if (isset($_GET['act']) && $_GET['act'] == 'delete') {
            $doc->title = __('Удаление пункта %s', $item_name);
            $form = new form('?menu=' . urlencode($menu) . '&amp;item=' . urlencode($item_name) . '&amp;' . passgen());
            $form->captcha();
            $form->button(__('Удалить'), 'delete');
            $form->display();
        } else {

            $form = new form('?menu=' . urlencode($menu) . '&amp;item=' . urlencode($item_name) . '&amp;' . passgen());

            $form->text('name', __('Название'), $item_name);
            $form->text('position', __('Позиция'), arraypos::getPosition($m_obj->menu_arr, $item_name));
            $form->text('url', __('Ссылка'), $item['url']);
            $form->text('counter', __('Ссылка счетчика'), @$item['counter']);
            $form->text('icon', __('Иконка'), $item['icon']);

            $form->checkbox('razdel', __('Раздел'), @$item['razdel']);
            $form->checkbox('is_vip', __('Только для VIP'), @$item['is_vip']);

            $options = array();
            foreach ($groups as $group => $value) {
                $options[] = array($group, $value['name'], $group == @$item['group']);
            }
            $form->select('group', __('Для группы (и выше)') . '*', $options);
            $form->bbcode('* ' . __('Регулируется только отображение ссылки'));
            $form->button(__('Сохранить'), 'save');
            $form->display();
        }

        $doc->ret(__('Меню "%s"', $menu), '?menu=' . urlencode($menu) . '&amp;' . passgen());
        $doc->ret(__('Список меню'), '?' . passgen());
        $doc->ret(__('Управление'), './?' . passgen());
        exit;
    }

    if (isset($_GET['item_add'])) {
        $doc->title = __('Новый пункт меню %s', $menu);
        if (!empty($_POST['create'])) {
            $name = text::input_text(@$_POST['name']);
            $url = text::input_text(@$_POST['url']);
            $icon = text::input_text(@$_POST['icon']);
            $counter = text::input_text(@$_POST['counter']);
            $razdel = (int) !empty($_POST['razdel']);
            $is_vip = (int) !empty($_POST['is_vip']);
            $group = (int) @$_POST['group'];
            $position = (int) @$_POST['position'];
            if (empty($name)) {
                $doc->err(__('Название не может быть пустым'));
            } elseif (isset($m_obj->menu_arr[$name])) {
                $doc->err(__('Выбранное название меню уже занято'));
            } else {
                $ini = ini::read(H . '/sys/ini/menu.' . $menu . '.ini', true);


                $ini[$name] = array('url' => $url,
                    'icon' => $icon,
                    'counter' => $counter,
                    'razdel' => $razdel,
                    'is_vip' => $is_vip,
                    'group' => $group
                );
                arraypos::setPosition($ini, $name, $position);
                if (ini::save(H . '/sys/ini/menu.' . $menu . '.ini', $ini, true)) {
                    $doc->msg(__('Изменения успешно приняты'));
                } else {
                    $doc->err(__('Ошибка при сохранении файла'));
                }
                header('Refresh: 1; url=?menu=' . urlencode($menu) . '&amp;' . passgen());
                $doc->ret(__('Меню "%s"', $menu), '?menu=' . urlencode($menu) . '&amp;' . passgen());
                $doc->ret(__('Список меню'), '?' . passgen());
                $doc->ret(__('Управление'), './?' . passgen());
                exit;
            }
        }

        $form = new form('?menu=' . urlencode($menu) . '&amp;item_add&amp;' . passgen());
        $form->text('name', __('Название'));
        $form->text('position', __('Позиция'), count($m_obj->menu_arr) + 1);
        $form->text('url', __('Ссылка'), '/');
        $form->text('counter', __('Ссылка счетчика'), '');
        $form->text('icon', __('Иконка'), 'square');

        $form->checkbox('razdel', __('Раздел'));
        $form->checkbox('is_vip', __('Только для VIP'));

        $options = array();
        foreach ($groups as $group => $value) {
            $options[] = array($group, $value['name']);
        }
        $form->select('group', __('Для группы (и выше)') . '*', $options);
        $form->bbcode('* ' . __('Регулируется только отображение ссылки'));
        $form->button(__('Создать'), 'create');
        $form->display();


        $doc->ret(__('Меню "%s"', $menu), '?menu=' . urlencode($menu) . '&amp;' . passgen());
        $doc->ret(__('Список меню'), '?' . passgen());
        $doc->ret(__('Управление'), './?' . passgen());
        exit;
    }



    if (isset($_GET['sortable'])) {

        $ini = ini::read(H . '/sys/ini/menu.' . $menu . '.ini', true);
        $doc->clean();

        //echo $_POST['sortable'];
        $sortable = explode(',', $_POST['sortable']);

        foreach ($sortable as $position => $key) {
            //$key = base64_decode($key);
            // echo "$position $key\n";
            arraypos::setPosition($ini, $key, $position + 1);
        }

        header('Content-type: application/json');
        if (ini::save(H . '/sys/ini/menu.' . $menu . '.ini', $ini, true)) {
            echo json_encode(array('result' => 1, 'description' => __('Изменения сохранены')));
        } else {
            echo json_encode(array('result' => 0, 'description' => __('Не удалось сохранить порядок пунктов в меню')));
        }


        exit;
    }

    if (isset($_GET['up']) || isset($_GET['down'])) {
        $ini = ini::read(H . '/sys/ini/menu.' . $menu . '.ini', true);

        if (isset($_GET['up'])) {
            $item_name = $_GET['up'];
            if (misc::array_key_move($ini, $item_name, - 1)) {
                $doc->msg(__('Пункт "%s" успешно перемещен вверх', $item_name));
            } else {
                $doc->err(__('Пункт "%s" уже находится вверху', $item_name));
            }
        }

        if (isset($_GET['down'])) {
            $item_name = $_GET['down'];
            if (misc::array_key_move($ini, $item_name, 1)) {
                $doc->msg(__('Пункт "%s" успешно перемещен вниз', $item_name));
            } else {
                $doc->err(__('Пункт "%s" уже находится внизу', $item_name));
            }
        }
        if (ini::save(H . '/sys/ini/menu.' . $menu . '.ini', $ini, true)) {
            $doc->msg(__('Изменения успешно приняты'));
        } else {
            $doc->err(__('Ошибка при сохранении файла'));
        }
        $m_obj = new menu_ini($menu);
    }


    $listing = new listing();

    $position = 0;
    foreach ($m_obj->menu_arr as $name => $item) {
        $position++;

        $post = $listing->post();
        $post->id = $name;
        $post->url = '?menu=' . urlencode($menu) . '&amp;item=' . urlencode($name);
        $post->title = text::toValue($name);

        $post->icon($item['icon']);

        $post->action('arrow-up', '?menu=' . urlencode($menu) . '&amp;up=' . urlencode($name) . '&amp;' . passgen());
        $post->action('arrow-down', '?menu=' . urlencode($menu) . '&amp;down=' . urlencode($name) . '&amp;' . passgen());
        $post->action('trash-o', '?menu=' . urlencode($menu) . '&amp;item=' . urlencode($name) . '&amp;act=delete&amp;' . passgen());



        if (empty($item['razdel'])) {
            $post->content = __('Ссылка') . ": $item[url]\n";
            if (!empty($item['icon'])) {
                $icon = $item['icon'];
            }
        } else {
            $post->content = "[b]" . __('Раздел') . "[/b]\n";
            $post->highlight = true;
        }

        if (!empty($item['for_vip'])) {
            $post->content .= __('Только для [b]VIP[/b]-пользователей') . "\n";
        }

        if (!empty($item['group'])) {
            $post->content .= __('Только для группы [b]%s[/b] (%s)', groups::name($item['group']), $item['group']) . " \n";
        }
        $post->content = text::toOutput($post->content);
    }


    $listing->sortable = '?sortable&amp;menu=' . urlencode($menu);
    $listing->display(__('Меню пусто'));

    $doc->opt(__('Добавить пункт'), '?menu=' . urlencode($menu) . '&amp;item_add&amp;' . passgen(), false, '<i class="fa fa-plus fa-fw"></i>');

    $doc->ret(__('Список меню'), '?' . passgen());
    $doc->ret(__('Управление'), './?' . passgen());
    exit;
}


$listing = new listing();
foreach ($menus as $menu) {
    $post = $listing->post();
    $post->title = text::toValue($menu);
    $post->url = '?menu=' . urlencode($menu);
    $post->icon('chevron-right');
}
$listing->display(__('Нет меню для редактирования'));

$doc->ret(__('Управление'), './?' . passgen());