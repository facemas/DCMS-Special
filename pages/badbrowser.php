<?php
include_once '../sys/inc/start.php';
$return = empty($_GET['return'])? '/' : $_GET['return'];
$o_v = preg_match('#NT#ui', @$_SERVER ['HTTP_USER_AGENT']) ? "12.18":"12.16";
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $user_language_pack->xml_lang ?>">
 <head>
  <title><? echo __('Вы используете устаревший браузер.'); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge"><!-- осел,б… -->
  <link rel="stylesheet" charset="utf-8" href="/sys/themes/.common/system.css" />
    <style type="text/css">
     html, body {
         width: 100%;
         height: 100%;
         background: #F7F7F7;
         font-size: 12pt;
         padding: 0;
         margin: 0;
     }

     #bad_browser {
         position: absolute;
         left: 50%;
         top: 50%;
         text-align: center;
         margin: -200px 0px 0px -250px;
         max-width: 500px;
         background: #FFF;
         line-height: 180%;
         border-bottom: 1px solid #E4E4E4;
         -webkit-box-shadow: 0 0 3px rgba(0, 0, 0, 0.15);
         -moz-box-shadow: 0 0 3px rgba(0, 0, 0, 0.15);
         box-shadow: 0 0 3px rgba(0, 0, 0, 0.15);
     }

     #content {
         padding: 20px;
         font-size: 1.19em;
     }

     #content div {
         margin: 10px 0 15px 0;
     }

     #content #browsers {
         width: 480px;
         height: 136px;
         margin: 15px auto 0;
     }

     #browsers a {
         float: left;
         width: 120px;
         height:142px;
         text-decoration: none;
         padding: 106px 0 13px 0;
         -webkit-border-radius: 4px;
         -khtml-border-radius: 4px;
         -moz-border-radius: 4px;
         border-radius: 4px;
     }

     #browsers a:hover {
         text-decoration: none;
         background-color: #edf1f5 !important;
     }

     .is_2x #browsers a {
         background-size: 80px 80px !important;
     }
    </style><!--[if lte IE 8]>
     <style>
     #bad_browser {
         border: none;
     }

     #wrap {
         border: solid #C3C3C3;
         border-width: 0 1px 1px;
     }

     #content {
         border: solid #D9E0E7;
         border-width: 0 1px 1px;
     }
  </style><![endif]-->
 </head><body>
  <div id="bad_browser">
   <div id="wrap">
    <div id="content"><?= __("Для работы с сайтом необходима поддержка JavaScript и Cookies.") . "\n"; ?>
     <div><?= __("Чтобы использовать все возможности сайта, загрузите и установите один из этих браузеров:") . "\n"; ?>
     <div id="browsers" style="width: 360px; text-align: center;">
     <a href="http://www.opera.com/download/guide/?ver=<?php echo $o_v; ?>" target="_blank" id="opera">Opera</a>
     <a href="http://www.mozilla.org/<?= $user_language_pack->xml_lang ?>/firefox/" target="_blank" id="firefox">Firefox</a>
     <a href="http://www.google.com/chrome/" target="_blank" id="chrome">Chrome</a>
    </div>
   </div>
  </div>
 </body>
</html>