
(function (user) {
    "use strict";

    var ajax_timeout = 5000;
    var translate = window.translate;

    $(function () {
        var $user = $('.user .nick');
        var $reg = $('.user .reg');
        var $login = $('.user .login');

        var $title = $('#title');

        var $mail_icon = $('#title .mail');
        var $not_icon = $('#title .notification');
        var $friend_icon = $('#title .friend');


        $mail_icon.on('click', function () {
            location.href = "/my.mail.php?only_unreaded";
            return false;
        });

        $not_icon.on('click', function () {
            location.href = "/my.notification.php";
            return false;
        });

        $friend_icon.on('click', function () {
            location.href = "/my.friends.php";
            return false;
        });

        $(document).on('userRefreshed', function (event, data) {
            if (data) {
                if (user.mail_new_count < data.mail_new_count) {
                    // звуковое уведомление, только в случае, если кол-во не прочитанных писем увеличилось
                    $(document).trigger('newMessage');
                }
                if (user.not_new_count < data.not_new_count) {
                    $(document).trigger('newMessage');
                }
                user = data;
            }

            $title.toggleClass('mail', +user.mail_new_count > 0);
            $title.toggleClass('notification', +user.not_new_count > 0);
            $title.toggleClass('friend', +user.friend_new_count > 0);

            if (user.id) {
                $user.text(user.login).show();
                $reg.hide();
                $login.hide();

                setTimeout(function () {
                    $(document).trigger('userRefresh');
                }, ajax_timeout);

            } else {
                $user.hide();
                $title.removeClass('mail', 'friend', 'notification');
                $reg.text(translate.reg).show();
                $login.text(translate.auth).show();
            }
        });

        $(document).on('userRefresh', function () {
            $().dcmsApi.request('api_user', 'get', Object.keys(user),
                    function (data) {
                        if (!data) {
                            return;
                        }
                        ajax_timeout = 5000;
                        $(document).trigger('userRefreshed', data);
                    }, function () {
                ajax_timeout = 60000;
                $(document).trigger('userRefreshed');
            }
            );
        });

        $(document).trigger('userRefreshed');
    });
})(window.user);