define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/collect/index',
                    add_url: 'user/collect/add',
                    // edit_url: 'user/collect/edit',
                    edit_url: false,
                    del_url: 'user/collect/del',
                    multi_url: 'user/collect/multi',
                    table: 'collect',
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
                        // {field: 'id', title: __('Id')},
                        // {field: 'goods_id', title: __('Goods_id')},
                        // {field: 'uid', title: __('Uid')},
                        {field: 'user.username', title: __('Username'),operate:false},
                        {field: 'user.mobile', title: __('Mobile'),operate:false},
                        {field: 'goods.images', title: __('Images'), formatter: Table.api.formatter.images, operate: false},
                        {field: 'goods.goods_name', title: __('Goods_name'),operate:'like'},
                        // {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        // {field: 'article_id', title: __('Article_id')},
                        // {field: 'type', title: __('Type')},
                        // {field: 'status', title: __('Status')},
                        // {field: 'item_status', title: __('Item_status')},
                        // {field: 'pid', title: __('Pid')},
                        {field: 'add_time', title: __('Add_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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