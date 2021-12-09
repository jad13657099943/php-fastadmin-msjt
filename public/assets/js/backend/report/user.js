define(['jquery', 'bootstrap', 'backend', 'echarts', 'echarts-theme', 'form'], function ($, undefined, Backend, Echarts, undefined, Form) {
    var Controller = {
        index: function () {

            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('test'), 'walden');
            var mode = 'day';
            var row = 7;

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '会员数据报表'
                },
                toolbox: {
                    show: true,
                    feature: {
                        magicType: {
                            show: true,
                            type: ['line', 'bar']
                        }
                    }
                },
                tooltip: {
                    trigger: 'axis',
                },
                legend: {
                    data: ['新用户', '活跃用户']
                },
                xAxis: {
                    boundaryGap: false,
                    data: [],
                },
                yAxis: {},
                series: [{
                    name: '新用户',
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        normal: {}
                    },
                    data: [],
                }, {
                    name: '活跃用户',
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        normal: {}
                    },
                    data: [],
                }]
            };

            myChart.setOption(option);
            Controller.api.updateData(mode, row, myChart);

            $(document).on('click', '.mode', function () {
                if ($(this).hasClass('active') == false) {
                    mode = $(this).data('mode');
                    row = $(this).data('row');

                    $('.mode.active').removeClass('active');
                    $(this).addClass('active');

                    Controller.api.updateData(mode, row, myChart);
                }
            });
            $('.datetimerange').on('change', function (e) {
                Controller.api.updateData(mode, row, myChart);
            });
            Controller.events.datetimerange();
        },
        api: {
            updateData: function (mode, row, myChart) {
                // myChart.showLoading();
                var startTime, endTime;
                var date = $('.datetimerange').val();
                if (date) {
                    date = date.split(' - ');
                    startTime = date[0];
                    endTime = date[1];
                }
                Fast.api.ajax({
                    url: 'report/user/getData',
                    data: {mode: mode, row: row, start: startTime, end: endTime},
                }, function (data) {
                    var option = {
                        xAxis: {data: data.time},
                        series: [{data: data.register}, {data: data.login}]
                    };
                    myChart.setOption(option);
                    // myChart.hideLoading();
                    return false;
                }, function () {
                    return false;
                })
            },
        },
        events: {
            datetimerange: function () {
                require(['bootstrap-daterangepicker'], function () {
                    var options = {
                        timePicker: false,
                        autoUpdateInput: false,
                        timePickerSeconds: true,
                        timePicker24Hour: true,
                        locale: {
                            format: 'YYYY-MM-DD',
                            customRangeLabel: __("Custom Range"),
                            applyLabel: __("Apply"),
                            cancelLabel: __("Clear"),
                            daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
                            monthNames: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
                        },
                    };
                    var origincallback = function (start, end) {
                        $(this.element).val(start.format(this.locale.format) + " - " + end.format(this.locale.format));
                        $(this.element).trigger('blur');
                    };
                    $(".datetimerange").each(function () {
                        var callback = typeof $(this).data('callback') == 'function' ? $(this).data('callback') : origincallback;
                        $(this).on('apply.daterangepicker', function (ev, picker) {
                            callback.call(picker, picker.startDate, picker.endDate);
                            $(this).trigger('change');
                        });
                        $(this).on('cancel.daterangepicker', function (ev, picker) {
                            $(this).val('').trigger('blur');
                            $(this).trigger('change');
                        });
                        $(this).daterangepicker($.extend({}, options, $(this).data()), callback);
                    });
                });
            }
        }
    };
    return Controller;
});