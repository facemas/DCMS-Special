<?php

require_once dirname(__FILE__) . '/../sys/inc/initialization.php';

 if ($_SESSION['language'] && languages::exists($_SESSION['language'])){
    $user_language_pack = new language_pack($_SESSION['language']);
} else {
    $user_language_pack = new language_pack('english');
}

/**
 * подключение к базе данных на этапе установки
 */
function db_connect() {
    $settings = &$_SESSION['settings'];
    
    /*
    mysql_connect($settings['mysql_host'], $settings['mysql_user'], $settings['mysql_pass']) or die(__('Нет соединения с сервером базы'));
    mysql_select_db($settings['mysql_base']) or die(__('Нет доступа к выбранной базе данных'));
    mysql_query('SET NAMES "utf8"');
    */

    try {
        $db = DB::me($settings['mysql_host'], $settings['mysql_base'], $settings['mysql_user'], $settings['mysql_pass']);
        $db->setAttribute(PDO :: ATTR_DEFAULT_FETCH_MODE, PDO :: FETCH_ASSOC);
        $db->query("SET NAMES utf8;");
        $dcms->db = $db;
    } catch (Exception $e) {
        die('Ошибка подключения к базе данных:' . $e->getMessage());
    }
}

if (is_file(H . '/sys/ini/settings.ini')) {
    header("Location: /?" . passgen() . '&' . SID);
    exit;
}

$install = &$_SESSION['install'];
$options = &$_SESSION['options'];
$ini = @parse_ini_file('inc/steps.ini', true);

foreach ($ini as $key => $value) {
    if (empty($value['if_option']) || isset($options[$value['if_option']])) {
        if (empty($install[$key]['status'])) {
            $step = $key;
            break;
        }
    }
}

header('Content-Type: application/xhtml+xml; charset=utf-8');
ob_start();
include 'inc/head.php';

echo "<h1>" . __($ini[$step]['title']) . "</h1>";

if (isset($_POST['to_start'])) {
    header("Location: ./?" . passgen() . '&' . SID);

    unset($_SESSION);
    session_destroy();

    /* Инициализация механизма сессий  */
    session_name(SESSION_NAME) or die(__('Невозможно инициализировать сессии'));
    @session_start() or die(__('Невозможно инициализировать сессии'));
    exit;
}
echo "<form class='form_header' method='post' action='?" . passgen() . "'>";
echo "<input type='submit' name='to_start' value='" . __('В начало') . "' />";
echo "<input type='submit' name='refresh' value='" . __('Обновить') . "' />";
echo "</form>";

if (!@include_once ('inc/' . $step . '.php'))
    die(__('Невозможно продолжить установку по причине отсутствия файла %s', 'inc/' . $step . '.php'));
$inst_obj = new $step;

if (isset($_POST['next_step'])) {
    $install[$step]['status'] = $inst_obj->actions();

    header("Location: ./?" . passgen() . '&' . SID);
    exit;
}

echo "<form class='form_content' method='post' action='?" . passgen() . "'><div class='form_content'>";

$returned = $inst_obj->form();

echo "</div>";

if ($returned)
    echo "<input type='submit' name='next_step' value='" . __('Далее') . "' />";
else
    echo "<input type='submit' name='refresh' value='" . __('Обновить') . "' />";
echo "</form>";

include 'inc/foot.php';
