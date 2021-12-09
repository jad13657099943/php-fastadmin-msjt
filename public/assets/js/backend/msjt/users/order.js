define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/users/order/index',
                    add_url: 'msjt/users/order/add',
                  //  edit_url: 'msjt/users/order/edit',
                    del_url: 'msjt/users/order/del',
                    multi_url: 'msjt/users/order/multi',
                    table: 'msjt_users_order',
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
                       // {field: 'id', title: __('Id')},
                       // {field: 'user_id', title: __('User_id')},
                        {field: 'order_no', title: __('Order_no')},
                        {field: 'user.nickname',title:__('用户昵称')},
                        {field:'user.mobile',title:__('用户手机号')},
                        {field: 'money', title: __('订单金额'), operate:false},
                        {field: 'createtime', title: __('下单时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},

                       // {field: 'goods_num', title: __('Goods_num'), operate:'BETWEEN'},
                       // {field: 'freight', title: __('Freight'), operate:'BETWEEN'},
                       // {field: 'pay_type', title: __('Pay_type')},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3'),"4":__('Status 4'),"5":__('Status 5'),"6":__('Status 6')}, formatter: Table.api.formatter.status},
                      //  {field:'pay_status',title:__('支付状态'),operate: false,searchList: {1:__('未支付'),2:__('已支付')}},
                        {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                       // {field: 'deliver_time', title: __('Deliver_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                       // {field: 'compelet_time', title: __('Compelet_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                       // {field: 'del_time', title: __('Del_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},

                       // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                      //  {field: 'pay_money', title: __('Pay_money'), operate:'BETWEEN'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                        buttons:[
                            {
                                name: 'info',
                                text: '查看详情',
                                title: '查看详情',
                               // extend:'data-area=\'["100%","100%"]\'',
                                classname: 'btn btn-xs btn-success btn-dialog info',
                                url: 'msjt/users/order/info'
                            },

                            {
                                name: 'deliver',
                                text: '确认发货',
                                title: '确认发货',
                                confirm:'确认发货',
                                // extend:'data-area=\'["100%","100%"]\'',
                                classname: 'btn btn-xs btn-success btn-ajax info',
                                url: 'msjt/users/order/setDeliver',
                                success:function (data,ret){
                                    Layer.alert(ret.msg);
                                    table.bootstrapTable('refresh',{})
                                },
                                visible:function (row) {
                                    if (row.status==2){
                                        return  true;
                                    }
                                    return false;
                                }
                            },
                        ],
                            formatter: Table.api.formatter.operate,
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
        deliver:function(){
            $('#deliver').click(function (){
                $.ajax({
                    url:'msjt/users/order/setDeliver',
                    datatype:'json',
                    type:'post',
                    data:{id:ids},
                    success:function (data){
                        Toastr.success(data.msg);
                        window.parent.location.reload();
                       // table.bootstrapTable('refresh',{})
                    },
                    error:function (data){

                    }
                });
            });
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