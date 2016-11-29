/**
 * Created by DES on 11.11.2015.
 */
(function () {
    "use strict";

    $(function () {
        $('.form .textarea .smiles_button').on('click', function () {
            var $p = $(this).parent();

            if ($p.hasClass('smiles')) {
                $p.removeClass('smiles');
            } else {
                $p.addClass('smiles');

                if ($p.data('smilesLoaded')) {
                    return;
                }

                var $smiles = $p.find('.smiles');
                $().dcmsApi.request('api_smiles', 'get', null, function (data) {
                    for (var i = 0; i < data.length; i++) {
                        var $smile = $('<img class="smile" src="' + data[i].image + '" />');
                        $smile.data(data[i]);
                        $smile.appendTo($smiles);
                    }
                    $smiles.on('click', '.smile', function () {
                        var data = $(this).data();
                        $p.removeClass('smiles');
                        window.InputInsert($p.find('textarea')[0], '', ' ' + data.code, true);
                    });
                    $p.data('smilesLoaded', true);
                });
            }
        });
    });
})();