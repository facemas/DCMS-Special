(function ($) {
    if (typeof $ == "undefined")
        return;
    var api = $.fn.dcmsApi = {
        _timeout: null,
        _queue: [],
        /**
         * Постановка запроса в очередь
         * @param {string} module Модуль, к которому обращаемся (php class в [/sys/plugins/classes/*] Должен быть реализацией интерфейса api_controller )
         * @param {string} method Метод, который будет вызываться у модуля (статичный метод класса)
         * @param {*} params Параметры, с которыми будет вызван метод
         * @param {function} callback Обработчик успешно выполненного запроса
         * @param {function} [callbackE] Обработчик ошибки при выполнении запроса
         */
        request: function (module, method, params, callback, callbackE) {
            if (api._timeout)
                clearTimeout(api._timeout);
            api._queue.push({
                module: module,
                method: method,
                p: params,
                c: callback,
                ce: callbackE
            });
            api._timeout = setTimeout(api.start, 20);
        },
        /**
         * Отправка очереди запросов на сервер
         */
        start: function () {
            var requests = [];
            var queue = api._queue;
            api._queue = [];
            for (var i = 0; i < queue.length; i++) {
                var req = queue[i];
                requests.push({
                    module: req.module,
                    method: req.method,
                    data: req.p
                });
            }
            $.post('/sys/api.php', {
                requests: JSON.stringify(requests)
            }).
                done($.proxy(api._done, this, queue)).
                fail($.proxy(api._fail, this, queue));
        },
        /**
         * Обработка ответа сервера и выполнение отдельных c`ов из очереди
         */
        _done: function (queue, response) {
            for (var i = 0; i < queue.length; i++) {
                try {
                    if (response[i].error)
                        queue[i].ce.call(this);
                    else
                        queue[i].c.call(this, response[i].data);
                } catch (e) {

                }
            }
        },
        _fail: function (queue) {
            for (var i = 0; i < queue.length; i++) {
                try {
                    queue[i].ce.call(this);
                } catch (e) {

                }
            }
        }
    };
})(jQuery);

// создаем модуль для AngularJs, который проксирует запросы через jQuery
if (window.angular){
    (function (angular) {
        if (typeof angular == "undefined")
            return;
        angular.module('dcmsApi', [])
            .factory('dcmsApi', function ($q) {
                var api = $().dcmsApi;
                return {
                    /**
                     * Запрос к API DCMS
                     */
                    request: function (module, method, params) {
                        var deferred = $q.defer();
                        api.request.call(this, module, method, params,
                            function (data) {
                                deferred.resolve(data);
                            }, function () {
                                deferred.reject();
                            });
                        return deferred.promise;
                    }
                };
            });
    })(window.angular);
}
