define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'school/index',
                    add_url: 'school/add',
                    edit_url: 'school/edit',
                    del_url: 'school/del',
                    multi_url: 'school/multi',
                    table: 'school',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                queryParams: function (params) { //传递ajax参数
                    params.filter = JSON.stringify({'id': Config.school_id});
                    params.op = JSON.stringify({'id': '='});
                    return params;
                },
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'school_name', title: __('School_name'), operate: "like"},
                        {
                            field: 'school_image',
                            title: __('School_image'),
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {field: 'school_desc', title: __('School_desc'), operate: false},

                        {field: 'school_freight', title: __('School_freight'), operate: false},
                        // {field: 'school_mobile', title: __('School_mobile')},
                        // {field: 'school_pwd', title: __('School_pwd')},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {10: __('Status 10'), 20: __('Status 20')},

                        },
                        {field: 'weigh', title: __('Weigh'), operate: false},
                        {field: 'school_address', title: __('School_address'), operate: false},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
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