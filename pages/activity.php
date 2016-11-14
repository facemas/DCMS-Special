<?php

$ank = (empty($_GET ['id'])) ? $user : new user((int) $_GET ['id']);

$from = 'activity';
$doc->tab(__('Активность'), '?act=activity&amp;id=' . $ank->id, $from === 'activity');
$doc->tab(__('Анкета'), '?act=anketa&amp;id=' . $ank->id, $from === 'anketa');
$doc->tab(__('Основное'), '?id=' . $ank->id, $from === 'default');

# Баллы
$post = $listing->post();
$post->title = __('Баллы');
$post->icon('gg-circle');
$post->counter = $ank->balls;

# Последний визит
$post = $listing->post();
$post->title = __('Последний визит');
$post->icon('history');
$post->counter = misc::when($ank->last_visit);

# Всего переходов
$post = $listing->post();
$post->title = __('Всего переходов');
$post->icon('street-view');
$post->counter = $ank->conversions;

# Дата регистрации
$post = $listing->post();
$post->title = __('Дата регистрации');
$post->icon('calendar-o');
$post->counter = date('d-m-Y', $ank->reg_date);
