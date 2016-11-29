/**
 * Created by DES on 11.11.2015.
 */
(function (window) {
    "use strict";

    $(function () {
        $('form').each(function () {
            var $element = $(this);
            var url = $element.attr('data-ajax-url');
            if (!url) {
                return;
            }

            $element.on('submit', function (event) {
                event.preventDefault();

                var formNode = event.target;
                var postData = {};
                for (var i = 0; i < formNode.elements.length; i++) {
                    postData[formNode.elements[i].name] = formNode.elements[i].value;
                }

                $element.attr('disabled', 'disabled');


                $.ajax({
                    url: url,
                    type: 'post',
                    data: postData,
                    success: function (data) {
                        if (data.msg) {
                            window.showMessage('info', data.msg, 5000);
                        }

                        if (data.err) {
                            window.showMessage('error', data.err, 5000);
                        }

                        for (var i = 0; i < formNode.elements.length; i++) {
                            var name = formNode.elements[i].name;
                            if (typeof data.form[name] === "undefined") {
                                continue;
                            }
                            formNode.elements[i].value = data.form[name];
                        }
                        $element.attr('disabled', false);
                        $(document).trigger('form_submit', $element.attr('id')); // Уведомляем о том, что форма была отправлена. Это событие должен слушать листинг
                    },
                    error: function () {
                        $element.attr('disabled', false);
                    }
                });
            });
        });
    });

})(window);