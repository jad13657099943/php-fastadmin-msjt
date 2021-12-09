define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'apply/integralorder/index',
                    add_url: 'apply/integralorder/add',
                  //  edit_url: 'apply/integralorder/edit',
                   // del_url: 'apply/integralorder/del',
                    multi_url: 'apply/integralorder/multi',
                    table: 'integral_order',
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
                    //    {field: 'goods_id', title: __('Goods_id')},
                        {field: 'order_sn', title: __('Order_sn')},
                        {field: 'order_status', title: __('Order_status'),
                            searchList: {
                                0: __('Order_status 0'),
                                1: __('Order_status 1'),
                                2: __('Order_status 2'),
                            },
                            formatter: Table.api.formatter.status},

                        {field: 'image', title: __('Image'), formatter: Table.api.formatter.image ,operate:false},
                        {field: 'num', title: __('Num')},
                        //{field: 'pay_integral', title: __('Pay_integral'),operate:false},

                       // {field: 'is_del', title: __('Is_del'), searchList: {"-1":__('Is_del -1'),"1":__('Is_del 1')}, formatter: Table.api.formatter.normal},
                        {field: 'receiver_name', title: __('Receiver_name')},
                       // {field: 'receiver_phone', title: __('Receiver_phone')},
                        //{field: 'receiver_site', title: __('Receiver_site')},
                      //  {field: 'uid', title: __('Uid')},
                        {field: 'title', title: __('Title')},
                        {field: 'integral', title: __('Integral')},
                       // {field: 'delivery_company', title: __('Delivery_company')},
                      //  {field: 'shipper_code', title: __('Shipper_code')},
                        //{field: 'receipt_time', title: __('Receipt_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                       // {field: 'remark', title: __('Remark')},
                        //{field: 'distributionstyle', title: __('Distributionstyle')},
                        //{field: 'distributionfee', title: __('Distributionfee'), operate:'BETWEEN'},
                       // {field: 'express_no', title: __('Express_no')},
                        {field: 'add_time', title: __('Add_time'), operate:false, addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'edit',
                                    text: __('立即发货'),
                                    icon: 'fa fa-wrench',
                                    classname: 'btn btn-xs btn-olive btn-dialog',
                                    url: 'apply/integralorder/edit',
                                    visible: function (row) {
                                        return row.order_status == 0 ? true : false;
                                    }
                                },
                                {
                                    name: 'edit',
                                    text: __('Detail'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-pencil',
                                    url: 'apply/integralorder/edit',
                                    visible: function (row) {
                                        return row.order_status == 0 ? false : true;
                                    }
                                },

                            ],
                             formatter: Table.api.formatter.buttons}
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