define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/users/withdraw/index',
                    add_url: 'msjt/users/withdraw/add',
                  //  edit_url: 'msjt/users/withdraw/edit',
                    del_url: 'msjt/users/withdraw/del',
                    multi_url: 'msjt/users/withdraw/multi',
                    table: 'msjt_users_withdraw',
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
                        {field: 'user.nickname',title: __('用户昵称'),operate: false},
                        {field: 'user.mobile',title:__('手机号'),operate: false},
                     //   {field: 'user_id', title: __('User_id')},
                        {field: 'type', title: __('Type'), searchList: {"1":__('Type 1'),"2":__('Type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3')}, formatter: Table.api.formatter.status},
                        {field: 'money', title: __('Money'), operate:false},
                        {field: 'createtime', title: __('申请时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                    //    {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'info',
                                    text: '查看详情',
                                    title: '查看详情',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-success btn-dialog info',
                                    url: 'msjt/users/withdraw/info'
                                },
                                {
                                    name: 'success',
                                    text: '已打款',
                                    title: '已打款',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    url: 'msjt/users/withdraw/setSuccess',
                                    confirm:'确认已打款',
                                    success:function (data,ret){
                                        Layer.alert(ret.msg);
                                        table.bootstrapTable('refresh',{});
                                    },
                                    visible:function (row) {
                                        if (row.status==1){
                                            return true;
                                        }
                                        return  false;
                                    }
                                },
                                {
                                    name: 'review',
                                    text: '拒绝',
                                    title: '拒绝',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-info btn-dialog info',
                                    url: 'msjt/users/withdraw/review',
                                    visible:function (row){
                                        if (row.status==1){
                                            return true;
                                        }
                                        return  false;
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.operate}
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
        review:function () {
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