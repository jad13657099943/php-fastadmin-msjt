define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/users/users/index',
                    add_url: 'msjt/users/users/add',
                    edit_url: 'msjt/users/users/edit',
                    del_url: 'msjt/users/users/del',
                    multi_url: 'msjt/users/users/multi',
                    table: 'msjt_users_users',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible:true,
                search:false,
                showToggle:false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate: false},
                        {field: 'head_image', title: __('Head_image'),operate:false, formatter: Table.api.formatter.image},
                        {field: 'nickname', title: __('Nickname')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'name', title: __('真实姓名')},
                        {field: 'card', title: __('身份证')},
                        {field: 'grade', title: __('Grade'), searchList: $.getJSON('msjt/users/grade/gradeList')},
                      //  {field: 'dai', title: __('Dai'), searchList: {"1":__('Dai 1'),"2":__('Dai 2')}, formatter: Table.api.formatter.normal},
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