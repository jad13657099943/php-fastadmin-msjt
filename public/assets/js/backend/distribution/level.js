define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'distribution/level/index',
                    add_url: 'distribution/level/add',
                    edit_url: 'distribution/level/edit',
                    del_url: 'distribution/level/del',
                    multi_url: 'distribution/level/multi',
                    table: 'distributor_level',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                commonSearch: false,
                pagination: false,
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'level', title: __('Level'), operate: false,
                        },
                        {
                            field: 'commission_rate', title: __('Commission_rate'), operate: false,
                            formatter: function (value) {
                                return value + "%";
                            }
                        },
                        {
                            field: 'invite', title: 'VIP邀请人数', operate: false,
                        },
                        {
                            field: 'vip_commission_rate', title: 'VIP佣金比例', operate: false,
                            formatter: function (value) {
                                return value + "%";
                            }
                        },
                        /*{
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: false,
                            // addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },*/
                        /*{
                            field: 'status',
                            title: __('Status'),
                            searchList: {'0': '禁用', '1': '正常'},
                            formatter: Table.api.formatter.status
                        },*/
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
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});