<?php
include_once '../sys/inc/start.php';
dpanel::check_access();
$doc = new document(6);
$doc->title = __('Параметры vk.com');

if (isset($_POST['save'])) {
    /** @var dcms $dcms */
    $dcms->vk_auth_enable = (int) !empty($_POST['vk_auth_enable']);
    $dcms->vk_reg_enable = (int) !empty($_POST['vk_reg_enable']);
    $dcms->vk_auth_email_enable = (int) !empty($_POST['vk_auth_email_enable']);
    $dcms->vk_app_id = (int) text::input_text($_POST['vk_app_id']);
    $dcms->vk_app_secret = text::input_text($_POST['vk_app_secret']);
    $dcms->vk_community = (int) text::input_text($_POST['vk_community']);
    $dcms->save_settings($doc);
}

$form = new form('?' . passgen());
$form->checkbox('vk_auth_enable', __('Разрешить авторизацию'), $dcms->vk_auth_enable);
$form->checkbox('vk_auth_email_enable', __('Разрешить авторизацию зарегистрированного пользователя при совпадении e-mail'), $dcms->vk_auth_email_enable);
$form->checkbox('vk_reg_enable', __('Разрешить регистрацию'), $dcms->vk_reg_enable);
$form->text('vk_app_id', __('ID приложения'), $dcms->vk_app_id);
$form->text('vk_app_secret', __('Защищенный ключ'), $dcms->vk_app_secret);
$form->text('vk_community', __('ID cообщества ВК'), $dcms->vk_community);
$form->button(__('Применить'), 'save');
$form->display();

$doc->ret(__('Админка'), '/dpanel/');