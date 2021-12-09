define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vip/user/index',
                    // add_url: 'vip/user/add',
                    edit_url: 'vip/user/edit',
                    del_url: 'vip/user/del',
                    multi_url: 'vip/user/multi',
                    table: 'user',
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
                        {field: 'avatar', title: __('Avatar'), operate: false},
                        {field: 'username', title: __('Username'),operate:'like'},
                        {field: 'mobile', title: __('Mobile')},
                        {
                            field: 'vip_type',
                            title: __('Vip_type'),
                            searchList: {'1': '普通VIP', '2': '尊享VIP'},
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'distributor', title: __('Distributor'),
                            searchList: {'0': '不是', '1': '是'},
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'status', title: __('Status'),
                            searchList: {'normal': '正常', 'locked': '禁用'},
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: false,
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