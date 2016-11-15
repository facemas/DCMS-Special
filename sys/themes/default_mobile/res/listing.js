/**
 * Created by DES on 11.11.2015.
 */
(function () {
    "use strict";

    var ajax_timeout = 7000;

    // при нажатии на ссылку добавляем к ней класс pressed
    $(document).on('touchstart touchend touchleave touchmove mouseleave', 'a', function (event) {
        var $tg = $(event.currentTarget);
        switch (event.type) {
            case 'touchstart':
                if ($tg.data('pressed')) {
                    return;
                }
                $tg.data('pressed', true);
                $tg.toggleClass('invert');
                break;
            case 'touchend':
            case 'touchleave':
            case 'touchmove':
            case 'mouseleave':
                if (!$tg.data('pressed')) {
                    return;
                }
                $tg.data('pressed', false);
                $tg.toggleClass('pressed');
                break;
        }
        event.stopPropagation();
    });

    $(function () {
        $(".comments").each(function () {
            var $element = $(this);
            var id_form = $element.attr('data-form-id');
            var url = $element.attr('data-ajax-url');
            if (!url) {
                return;
            }
            var timeout;

            $(document).on('form_submit', function (event, id_form_arg) {
                if (id_form_arg === id_form) {
                    refresh(true);
                }
            });

            var refresh = function (forcibly) {
                clearTimeout(timeout);

                var skip_ids = [];
                $element.children().each(function () {
                    skip_ids.push(this.id);
                });

                $.ajax({
                    url: url,
                    type: 'post',
                    data: {skip_ids: skip_ids.join(',')},
                    success: function (data) {
                        var i;

                        if (data.remove && data.remove.length) {
                            for (i = 0; i < data.remove.length; i++) {
                                $('#' + data.remove[i]).remove();
                            }
                        }

                        if (data.add && data.add.length) {
                            for (i = 0; i < data.add.length; i++) {
                                var after_id = data.add[i].after_id;
                                var $el = $(data.add[i].html).css('opacity', '0');

                                if (after_id) {
                                    $element.children('#' + after_id).after($el);
                                } else {
                                    $el.prependTo($element);
                                }
                                $el.animate({opacity: 1}, 500);
                            }

                            if (!forcibly) {
                                $(document).trigger('newMessage');
                            }
                        }

                        timeout = setTimeout(refresh, ajax_timeout);
                    },
                    error: function () {
                        timeout = setTimeout(refresh, 60000);
                    }
                });
            };

            timeout = setTimeout(refresh, ajax_timeout);
        });

        $(".listing").each(function () {
            var $element = $(this);
            var id_form = $element.attr('data-form-id');
            var url = $element.attr('data-ajax-url');
            if (!url) {
                return;
            }
            var timeout;

            $(document).on('form_submit', function (event, id_form_arg) {
                if (id_form_arg === id_form) {
                    refresh(true);
                }
            });

            var refresh = function (forcibly) {
                clearTimeout(timeout);

                var skip_ids = [];
                $element.children().each(function () {
                    skip_ids.push(this.id);
                });

                $.ajax({
                    url: url,
                    type: 'post',
                    data: {skip_ids: skip_ids.join(',')},
                    success: function (data) {
                        var i;

                        if (data.remove && data.remove.length) {
                            for (i = 0; i < data.remove.length; i++) {
                                $('#' + data.remove[i]).remove();
                            }
                        }

                        if (data.add && data.add.length) {
                            for (i = 0; i < data.add.length; i++) {
                                var after_id = data.add[i].after_id;
                                var $el = $(data.add[i].html).css('opacity', '0');

                                if (after_id) {
                                    $element.children('#' + after_id).after($el);
                                } else {
                                    $el.prependTo($element);
                                }
                                $el.animate({opacity: 1}, 500);
                            }

                            if (!forcibly) {
                                $(document).trigger('newMessage');
                            }
                        }

                        timeout = setTimeout(refresh, ajax_timeout);
                    },
                    error: function () {
                        timeout = setTimeout(refresh, 60000);
                    }
                });
            };

            timeout = setTimeout(refresh, ajax_timeout);
        });
    });
})();