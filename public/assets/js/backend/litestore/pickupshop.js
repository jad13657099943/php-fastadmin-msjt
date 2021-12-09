define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'litestore/pickupshop/index',
                    add_url: 'litestore/pickupshop/add',
                    edit_url: 'litestore/pickupshop/edit',
                    del_url: 'litestore/pickupshop/del',
                    multi_url: 'litestore/pickupshop/multi',
                    table: 'pickup_shop',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'store_name', title: __('Store_name')},
                        {field: 'mobile', title: __('手机号')},
                        {field: 'opening_hours', title: __('Opening_hours'),operate:false},
                        {field: 'closing_hours', title: __('Closing_hours'),operate:false},
                        {field: 'address', title: __('Address')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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