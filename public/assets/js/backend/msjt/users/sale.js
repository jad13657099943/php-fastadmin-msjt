define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/users/sale/index',
                    add_url: 'msjt/users/sale/add',
                  //  edit_url: 'msjt/users/sale/edit',
                    del_url: 'msjt/users/sale/del',
                    multi_url: 'msjt/users/sale/multi',
                    table: 'msjt_users_sale',
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
                        {field: 'order_no', title: __('Order_no')},
                        {field: 'sale_no', title: __('Sale_no')},
                     //   {field: 'user_id', title: __('User_id')},
                        {field: 'sale_money', title: __('Sale_money'), operate:false},
                     //   {field: 'saleimages', title: __('Saleimages'), formatter: Table.api.formatter.images},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3'),"4":__('Status 4')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                   //     {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                    //    {field: 'order_goods_id', title: __('Order_goods_id')},
                  //      {field: 'refund_money', title: __('Refund_money'), operate:'BETWEEN'},
                  //      {field: 'refund_time', title: __('Refund_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                  //      {field: 'review_time', title: __('Review_time'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'info',
                                    text: '查看详情',
                                    title: '查看详情',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-success btn-dialog info',
                                    url: 'msjt/users/sale/info'
                                },
                                {
                                    name: 'refund',
                                    text: '退款',
                                    title: '退款',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-primary btn-ajax',
                                    url: 'msjt/users/sale/refund',
                                    confirm:'确认退款',
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
                                    url: 'msjt/users/sale/review',
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