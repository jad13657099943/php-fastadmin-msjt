define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'litestore/comment/index',
                    add_url: 'litestore/comment/add',
                 //   edit_url: 'litestore/comment/edit',
                    del_url: 'litestore/comment/del',
                   // multi_url: 'litestore/comment/multi',
                    table: 'comment',
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
                        {field: 'content', title: __('Content'),cellStyle: {css: {"min-width":"50px"}},operate:false},
                        {field: 'images', title: __('Images'), formatter: Table.api.formatter.images,operate:false},
                        {field: 'user_name', title: __('User_name')},

                        {field: 'user_head', title: __('User_head'), formatter: Table.api.formatter.images, operate: false},
                        {field: 'add_time', title: __('Add_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'star_num', title: __('Star_num'), operate:false},
                        {field: 'goods_sku', title: __('Goods_sku'),operate:false},
                      //  {field: 'like', title: __('Like'),operate:false},
                       // {field: 'comments', title: __('Comments'),operate:false},
                        {field: 'goods_name', title: __('Goods_name')},
                        {field: 'goods_image', title: __('Goods_image'), formatter: Table.api.formatter.image,operate:false},
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