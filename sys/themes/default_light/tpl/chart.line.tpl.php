<div id="<?= $id ?>" class="chart"></div>
<noscript>
    Для отображения графика необходим JavaScript
</noscript>
<script>
    $(document).on('highchartsLoaded', function () {
        $('#<?=$id?>').highcharts({
            title: {
                text: <?=json_encode($title)?>
            },
            xAxis: {
                categories: <?=json_encode($categories)?>
            },
            yAxis: {
                title: {
                    text: <?=json_encode($y_text)?>
                },
                plotLines: [
                    {
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }
                ]
            },
            tooltip: {
                valueSuffix: <?=json_encode($value_suffix)?>
            },
            series: <?=json_encode($series)?>
        });
    });

    $(function () {
        if ($.fn.highcharts || window.highchartsLoading)
            return;
        window.highchartsLoading = true;
        $.ajax({
            url: '/sys/themes/.common/highcharts.js',
            dataType: "script",
            cache: true,
            ifModified: true,
            success: function () {
                delete window.highchartsLoading;
                $(document).trigger('highchartsLoaded');
            }
        });
    });
</script>