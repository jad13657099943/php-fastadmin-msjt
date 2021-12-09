define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/goods/type/index',
                    add_url: 'msjt/goods/type/add',
                    edit_url: 'msjt/goods/type/edit',
                    del_url: 'msjt/goods/type/del',
                    multi_url: 'msjt/goods/type/multi',
                    table: 'msjt_goods_type',
                    dragsort_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                searchFormVisible:false,
                search:false,
                showToggle:false,
                showColumns: false,
                showExport: false,
                escape:false,
                commonSearch: false,
                visible: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                     //   {field: 'pid', title: __('Pid')},
                        {field: 'simage', title: __('Simage'), formatter: Table.api.formatter.image},
                     //   {field: 'is_recommend_data', title: __('Is_recommend_data'), searchList: {"1":__('Is_recommend_data 1'),"2":__('Is_recommend_data 2')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                      //  {field: 'weigh', title: __('Weigh')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                   //     {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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