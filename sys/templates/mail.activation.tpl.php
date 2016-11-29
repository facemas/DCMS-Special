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
        <b><?= $login ?></b>, Вы успешно зарегистрированы на сайте <b><?= $site ?></b>.<br />
        Пароль для входа: <b><?= $password ?></b><br />
        Для активации аккаунта необходимо перейти по ссылке: <b><a href="<?= $url ?>"><?= $url ?></a></b><br />
    </body>
</html>