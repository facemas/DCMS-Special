<?php

include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(groups::max());
$doc->title = __('Поддомены');

$browser_types = array('light', 'mobile', 'full');

if (!$dcms->check_domain_work)
    $dcms->check_domain_work = passgen();

/**
 * Проверка доступности домена, а также проверка что по этому домену открывается сайт с данной системой
 * @global \dcms $dcms
 * @param string $domain
 * @return boolean
 */
function domain_check($domain) {
    global $dcms;
    $http = new http_client('http://' . $domain . '/?check_domain_work');
    $http->timeout = 10;
    return $dcms->check_domain_work === $http->getContent();
}

if (isset($_POST ['save'])) {

    $subdomain_theme_redirect_old = $dcms->subdomain_theme_redirect;
    $dcms->subdomain_theme_redirect = (int) !empty($_POST ['subdomain_theme_redirect']);
    $dcms->subdomain_replace_url = (int) !empty($_POST ['subdomain_replace_url']);

    $subdomain_light_enable_old = $dcms->subdomain_light_enable;
    $dcms->subdomain_light_enable = (int) !empty($_POST ['subdomain_light_enable']);

    $subdomain_mobile_enable_old = $dcms->subdomain_mobile_enable;
    $dcms->subdomain_mobile_enable = (int) !empty($_POST ['subdomain_mobile_enable']);

    $subdomain_full_enable_old = $dcms->subdomain_full_enable;
    $dcms->subdomain_full_enable = (int) !empty($_POST ['subdomain_full_enable']);


    $dcms->subdomain_main = text::input_text($_POST ['subdomain_main']);

    $subdomain_light_old = $dcms->subdomain_light;
    $dcms->subdomain_light = text::input_text($_POST ['subdomain_light']);

    $subdomain_mobile_old = $dcms->subdomain_mobile;
    $dcms->subdomain_mobile = text::input_text($_POST ['subdomain_mobile']);

    $subdomain_full_old = $dcms->subdomain_full;
    $dcms->subdomain_full = text::input_text($_POST ['subdomain_full']);

    if ($dcms->subdomain_theme_redirect && $dcms->subdomain_theme_redirect != $subdomain_theme_redirect_old) {
        if (!$dcms->subdomain_main) {
            $doc->err(__('Основной домен не введен'));
            $dcms->subdomain_theme_redirect = 0;
        } elseif (!domain_check($dcms->subdomain_main)) {
            $doc->err(__('Основной домен не открывает данный сайт'));
            $dcms->subdomain_theme_redirect = 0;
        }
    }

    $pattern_need = "Поддомен для %s тем оформления не задан";
    $pattern_not_opening = "Поддомен для %s тем оформления не открывает данный сайт";

    if ($dcms->subdomain_light_enable && ($dcms->subdomain_light_enable != $subdomain_light_enable_old || $subdomain_light_old != $dcms->subdomain_light )) {
        if (!$dcms->subdomain_light) {
            $doc->err(__($pattern_need, 'WAP (light)'));
            $dcms->subdomain_light_enable = 0;
        } elseif (!domain_check($dcms->subdomain_light . '.' . $dcms->subdomain_main)) {
            $doc->err(__($pattern_not_opening, 'WAP (light)'));
            $dcms->subdomain_light_enable = 0;
        }
    }
    if ($dcms->subdomain_mobile_enable && ($dcms->subdomain_mobile_enable != $subdomain_mobile_enable_old || $subdomain_mobile_old != $dcms->subdomain_mobile )) {
        if (!$dcms->subdomain_mobile) {
            $doc->err(__($pattern_need, 'Touch (mobile)'));
            $dcms->subdomain_mobile_enable = 0;
        } elseif (!domain_check($dcms->subdomain_mobile . '.' . $dcms->subdomain_main)) {
            $doc->err(__($pattern_not_opening, 'Touch (mobile)'));
            $dcms->subdomain_mobile_enable = 0;
        }
    }
    if ($dcms->subdomain_full_enable && ($dcms->subdomain_full_enable != $subdomain_full_enable_old || $subdomain_full_old != $dcms->subdomain_full )) {
        if (!$dcms->subdomain_full) {
            $doc->err(__($pattern_need, 'WEB (full)'));
            $dcms->subdomain_full_enable = 0;
        } elseif (!domain_check($dcms->subdomain_full . '.' . $dcms->subdomain_main)) {
            $doc->err(__($pattern_not_opening, 'WEB (full)'));
            $dcms->subdomain_full_enable = 0;
        }
    }

    $dcms->save_settings($doc);
}


$form = new form('?' . passgen());
$form->text('subdomain_main', __('Основной домен'), $dcms->subdomain_main);
$form->checkbox('subdomain_theme_redirect', __('При переходе на главный домен переадресовывать на поддомен в соответствии с автоматически определенным типом браузера'), $dcms->subdomain_theme_redirect);
$form->checkbox('subdomain_replace_url', __('Удалять поддомен из ссылок'), $dcms->subdomain_replace_url);

foreach ($browser_types as $b_type) {
    $key_subdomain = 'subdomain_' . $b_type;
    $key_enable = 'subdomain_' . $b_type . '_enable';
    $form->text($key_subdomain, __('Поддомен %s (*.%s)', strtoupper($b_type), $dcms->subdomain_main), $dcms->$key_subdomain);
    $form->checkbox($key_enable, __('Выбирать %s тему при переходе по данному поддомену', strtoupper($b_type)), $dcms->$key_enable);
}

$form->button(__('Сохранить'), 'save');
$form->display();

$doc->ret(__('Управление'), '/dpanel/');
