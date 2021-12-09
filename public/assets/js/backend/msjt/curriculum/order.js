define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/curriculum/order/index',
                    add_url: 'msjt/curriculum/order/add',
                //    edit_url: 'msjt/curriculum/order/edit',
                    del_url: 'msjt/curriculum/order/del',
                    multi_url: 'msjt/curriculum/order/multi',
                    table: 'msjt_goods_curriculum_order',
                    dragsort_url: '',
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
                     //   {field: 'id', title: __('Id')},
                      //  {field: 'user_id', title: __('User_id')},
                        {field: 'order_no', title: __('Order_no')},
                        {field:'user.nickname',title:__('用户昵称')},
                        {field:'user.mobile',title:__('用户手机号')},
                      //  {field: 'curriculum_id', title: __('Curriculum_id')},
                        {field: 'money', title: __('订单金额'), operate:false},
                      //  {field: 'pay_type', title: __('Pay_type')},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                    //    {field: 'del_time', title: __('Del_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                    //    {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                     //   {field: 'pay_money', title: __('Pay_money'), operate:'BETWEEN'},
                     //   {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'info',
                                    text: '查看详情',
                                    title: '查看详情',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-success btn-dialog info',
                                    url: 'msjt/curriculum/order/info'
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});