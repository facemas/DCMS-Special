/**
 * Created by DES on 11.11.2015.
 */
(function(window){
    "use strict";

    $(function () {
        var prefixes = 'transform WebkitTransform MozTransform OTransform'.split(' ');
        for (var i = 0; i < prefixes.length; i++) {
            if (document.createElement('div').style[prefixes[i]] !== undefined) {
                $(document.body).addClass('transformSupport');
                break;
            }
        }
    });

    $(document).on('click', '.thumb_down', function (event) {
        if (!window.confirm(window.translate.rating_down_message)) {
            event.preventDefault();
            event.stopPropagation();
        }
    });

    $(document).on('click', '.tIcon.left', function (event) {
        $(".tIcon.left").toggleClass('active');
        event.stopPropagation();
    });

    $(document).on('click', '.tIcon.menu, body.menu_show', function (event) {
        $("body").toggleClass('menu_show');
        event.stopPropagation();
    });

    function showMessage(cl, text, timeout) {
        var $div = $('<div/>', {class: cl, text: text, css: {opacity: 0}}).appendTo("#messages");
        $div.animate({'opacity': 1}, 200);
        setTimeout(function () {
            $div.animate({'opacity': 0}, 200, undefined, function(){
                $div.remove();
            });
        }, timeout);
    }

    $(document).on('newMessage', function () {
        if (window.navigator.vibrate) {
            window.navigator.vibrate([100, 100]);
        }
        var audio = document.querySelector("#audio_notify");
        audio.pause();
        audio.loop = false;
        audio.currentTime = 0;
        audio.play();
    });


    window.showMessage = showMessage;
})(window);