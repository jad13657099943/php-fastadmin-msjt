define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/agency/users/index/ids/' + Config.ids,
                    add_url: 'msjt/agency/users/add',
                    //  edit_url: 'msjt/agency/users/edit',
                    //  del_url: 'msjt/agency/users/del',
                    multi_url: 'msjt/agency/users/multi',
                    table: 'msjt_users_users',
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
                        {
                            field: 'head_image',
                            title: __('Head_image'),
                            operate: false,
                            formatter: Table.api.formatter.image
                        },
                        {field: 'nickname', title: __('Nickname')},
                        //  {field: 'name', title: __('Name')},
                        //  {field: 'card', title: __('Card')},
                        {field: 'mobile', title: __('Mobile')},
                        //   {field: 'openid', title: __('Openid')},
                        {field: 'balance', title: __('佣金余额'), operate: false},
                        //  {field: 'grade', title: __('Grade'), searchList: {"1":__('Grade 1'),"2":__('Grade 2')}, formatter: Table.api.formatter.normal},
                        //   {field: 'dai', title: __('Dai'), searchList: {"1":__('Dai 1'),"2":__('Dai 2')}, formatter: Table.api.formatter.normal},
                        //   {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        //  {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        //  {field: 'pid', title: __('Pid')},

                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'balance',
                                    text: '佣金明细',
                                    title: '佣金明细',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-success btn-dialog info',
                                    url: 'msjt/agency/balance/index'
                                },
                                {
                                    name: 'agency',
                                    text: '分销订单',
                                    title: '分销订单',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-success btn-dialog info',
                                    url: 'msjt/agency/agency/index'
                                },
                                {
                                    name: 'team',
                                    text: '我的团队',
                                    title: '我的团队',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-success btn-dialog info',
                                    url: 'msjt/agency/users/index'
                                },
                            ],
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