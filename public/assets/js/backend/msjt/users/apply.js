define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/users/apply/index',
                    // add_url: 'msjt/users/apply/add',
                    add_url: '',
                    // edit_url: 'msjt/users/apply/edit',
                    edit_url: '',
                    del_url: 'msjt/users/apply/del',
                    multi_url: 'msjt/users/apply/multi',
                    table: 'msjt_users_apply',
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
                        // {field: 'user_id', title: __('User_id')},
                        {field: 'users.nickname', title: __('用户昵称'), operate: false},
                        {field: 'name', title: __('Name'), operate: false},
                        {field: 'mobile', title: __('Mobile'), operate: false},
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {"1": __('Status 1'), "2": __('Status 2'), "3": __('Status 3')},
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            buttons: [
                                {
                                    name: 'success',
                                    text: '通过',
                                    title: '通过',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    url: 'msjt/users/apply/setSuccess',
                                    confirm: '确认通过',
                                    success: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        table.bootstrapTable('refresh', {});
                                    },
                                    visible: function (row) {
                                        if (row.status == 1) {
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'review',
                                    text: '拒绝',
                                    title: '拒绝',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-info btn-dialog info',
                                    url: 'msjt/users/apply/review',
                                    visible: function (row) {
                                        if (row.status == 1) {
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                            ],
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
        review: function () {
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