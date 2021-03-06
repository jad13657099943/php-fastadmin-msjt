define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/crontab/index',
                    add_url: 'general/crontab/add',
                    edit_url: 'general/crontab/edit',
                    del_url: 'general/crontab/del',
                    multi_url: 'general/crontab/multi',
                    table: 'crontab'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'weigh',
                search: true,
                searchFormVisible: false,
                columns: [
                    [
                        {field: 'state', checkbox: true,},
                        {field: 'id', title: 'ID', operate: false},
                        {field: 'type_text', title: __('Type'), operate: false},
                        {field: 'title', title: __('Title')},
                        {
                            field: 'maximums',
                            title: __('Maximums'),
                            formatter: Controller.api.formatter.maximums,
                            operate: false
                        },
                        {field: 'executes', title: __('Executes'), operate: false},
                        {
                            field: 'begintime',
                            title: __('Begin time'),
                            formatter: Table.api.formatter.datetime,
                            addclass: 'datetimerange',
                            operate: false
                        },
                        {
                            field: 'endtime',
                            title: __('End time'),
                            formatter: Table.api.formatter.datetime,
                            addclass: 'datetimerange',
                            operate: false
                        },
                        {
                            field: 'nexttime',
                            title: __('Next execute time'),
                            formatter: Table.api.formatter.datetime,
                            addclass: 'datetimerange',
                            sortable: true,
                            operate: false
                        },
                        {
                            field: 'executetime',
                            title: __('Execute time'),
                            formatter: Table.api.formatter.datetime,
                            addclass: 'datetimerange',
                            sortable: true,
                            addclass: 'datetimerange',
                            operate: false
                        },
                        {field: 'weigh', title: __('Weigh'), operate: false},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status, operate: false},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                $('#schedule').on('valid.field', function (e, result) {
                    $("#pickdays").trigger("change");
                });
                Form.api.bindevent($("form[role=form]"));
                $(document).on("change", "#pickdays", function () {
                    $("#scheduleresult").html(__('Loading'));
                    $.post("general/crontab/get_schedule_future", {
                        schedule: $("#schedule").val(),
                        days: $(this).val()
                    }, function (ret) {
                        $("#scheduleresult").html("");
                        if (typeof ret.futuretime !== 'undefined' && $.isArray(ret.futuretime)) {
                            $.each(ret.futuretime, function (i, j) {
                                $("#scheduleresult").append("<li class='list-group-item'>" + j + "<span class='badge'>" + (i + 1) + "</span></li>");
                            });
                        }
                    }, 'json');

                });
                $("#pickdays").trigger("change");
            },
            formatter: {
                maximums: function (value, row, index) {
                    return value === 0 ? __('No limit') : value;
                }
            }
        }
    };
    return Controller;
});