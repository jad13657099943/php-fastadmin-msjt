define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/agency/agency/index',
                    add_url: 'msjt/agency/agency/add',
                 //   edit_url: 'msjt/agency/agency/edit',
                  //  del_url: 'msjt/agency/agency/del',
                    multi_url: 'msjt/agency/agency/multi',
                    table: 'msjt_users_order_agency',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
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
                       // {checkbox: true},
                      //  {field: 'id', title: __('Id')},
                      //  {field: 'user_id', title: __('User_id')},
                      //  {field: 'users', title: __('Users')},
                        {field: 'order_no', title: __('Order_no')},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'bl', title: __('Bl'), operate:'BETWEEN'},
                        {field: 'agency', title: __('Agency'), operate:'BETWEEN'},
                        {field: 'type', title: __('Type'), searchList: {"1":__('Type 1'),"2":__('Type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"1":__('待结算'),"2":__('已结算')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('产生时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                       // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                      //  {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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