define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/users/site/index',
                    add_url: 'msjt/users/site/add',
                    edit_url: 'msjt/users/site/edit',
                    del_url: 'msjt/users/site/del',
                    multi_url: 'msjt/users/site/multi',
                    table: 'msjt_users_site',
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
                      //  {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                      //  {field: 'pid', title: __('Pid')},
                       // {field: 'weigh', title: __('Weigh')},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                      //  {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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