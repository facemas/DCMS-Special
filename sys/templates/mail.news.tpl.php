<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title><?= $title ?> - <?= $site ?></title>
        <style type="text/css">
            /* <![CDATA[ */
            body {
                font-family: tahoma, arial, verdana, sans-serif, Lucida Sans;
                font-size: 14px;
            }
            #unsubscribe{
                font-size: small;
            }

            /* ]]> */
        </style>
    </head>
    <body>
        <div>Уведомляем Вас о новостях:</div>
        <p><?= $content ?></p>
        <div id="unsubscribe">Если вы больше не хотите получать сообщения о новостях на email <?= $email ?>, перейдите по <a href="<?= $unsubscribe ?>">ссылке</a>.</div>
    </body>
</html>