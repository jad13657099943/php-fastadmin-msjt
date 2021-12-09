define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'distribution/withdraw/index',
                    /* add_url: 'distribution/withdraw/add',
                     edit_url: 'distribution/withdraw/edit',*/
                    // del_url: 'distribution/withdraw/del',
                    multi_url: 'distribution/withdraw/multi',
                    table: 'withdraw',
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
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'realname', title: '姓名'},
                        {field: 'money', title: __('Money'), operate: false},
                        {
                            field: 'type',
                            title: '提现类型',
                            searchList: {'1': '微信', '2': '银行卡'},
                            formatter: Table.api.formatter.normal,
                            operate: false
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {
                                1: __('Status 1'),
                                2: __('Status 2'),
                                3: __('Status 3'),
                                4: '待转款',
                                5: '已完成',
                                6: '转款失败'
                            }
                        },
                        {
                            field: 'create_time',
                            title: __('Add_time'),
                            operate: false,
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate', title: __('Operate'), table: table, buttons: [
                                {
                                    name: 'detail',
                                    text: __('审核'),
                                    classname: 'btn btn-xs btn-warning btn-dialog',
                                    icon: 'fa fa-wrench',
                                    url: 'distribution/withdraw/edit',
                                    visible: function (row) {
                                        return row.status == 1 ? true : false;
                                    }
                                },
                                {
                                    name: 'detail',
                                    text: '确认转款',
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-pencil',
                                    url: 'distribution/withdraw/edit',
                                    visible: function (row) {
                                        return row.status == 4 ? true : false;
                                    }
                                },

                                {
                                    name: 'detail',
                                    text: __('详情'),
                                    classname: 'btn btn-xs btn-red btn-dialog',
                                    icon: 'fa fa-envira',
                                    url: 'distribution/withdraw/edit',
                                    visible: function (row) {
                                        return row.status != 1 ? true : false;
                                    }
                                },
                            ],
                            events: Table.api.events.operate, formatter: Table.api.formatter.operate
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