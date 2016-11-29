<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?= $title ?></title>
        <style type="text/css">
            /* <![CDATA[ */
            body{
                font-family: tahoma, arial, verdana, sans-serif, Lucida Sans;
                font-size: 14px;
            }
            /* ]]> */
        </style>
    </head>
    <body>
        <b><?= $login ?></b>, Вы запросили восстановление пароля на сайте <b><?= $site ?></b>.<br />
        Для ввода нового пароля перейдите по ссылке: <b><a href="<?= $url ?>"><?= $url ?></a></b><br />
        <br />
        Браузер: <?= $dcms->browser ?><br />
        IP: <?= long2ip($dcms->ip_long) ?><br />
    </body> 
</html>