define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/agency/balance/index/ids/'+Config.ids,
                   add_url: 'msjt/agency/balance/add',
                  //  edit_url: 'msjt/agency/balance/edit',
                  //  del_url: 'msjt/agency/balance/del',
                    multi_url: 'msjt/agency/balance/multi',
                    table: 'msjt_users_order_balance',
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
                        //{checkbox: true},
                     //   {field: 'id', title: __('Id')},
                     //   {field: 'user_id', title: __('User_id')},
                        {field: 'name', title: __('Name')},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'createtime', title: __('产生时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                      //  {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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