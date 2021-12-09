define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/goods/goods/index',
                    add_url: 'msjt/goods/goods/add',
                    edit_url: 'msjt/goods/goods/edit',
                    del_url: 'msjt/goods/goods/del',
                    multi_url: 'msjt/goods/goods/multi',
                    table: 'msjt_goods_goods',
                    dragsort_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                searchFormVisible:true,
                search:false,
                showToggle:false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate: false},
                        {field: 'name', title: __('Name')},
                        {field: 'type_id', title: __('Type_id'),searchList: $.getJSON('msjt/goods/type/typeLists')},
                        {field: 'simages', title: __('Simages'),operate:false, formatter: Table.api.formatter.images},
                    //    {field: 'fimages', title: __('Fimages'), formatter: Table.api.formatter.images},
                     //   {field: 'configjson', title: __('Configjson')},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                    //    {field: 'weigh', title: __('Weigh')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                     //   {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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