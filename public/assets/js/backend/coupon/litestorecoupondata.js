define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'coupon/litestorecoupondata/index',
                    add_url: 'coupon/litestorecoupondata/add',
                    edit_url: false,
                    del_url: 'coupon/litestorecoupondata/del',
                    multi_url: 'coupon/litestorecoupondata/multi',
                    table: 'litestore_coupon_data',
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
                        // {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('User_id'), addClass: "selectpage", extend: "data-source='user/user/index' data-field='mobile'", visible: false},
                        {field: 'user.nickname', title: __('User_id'), operate: false},
                        {field: 'user.mobile', title: __('User_mobile'), operate: false},
                        {field: 'litestore_coupon_id', title: __('Litestore_coupon_id'), addClass: "selectpage", extend: "data-source='coupon/litestorecoupon/index' data-field='name'", visible: false},
                        {field: 'coupon.name', title: __('Litestore_coupon_id'), operate: false},
                        {field: 'get_type', title: __('Get_type'), searchList: {"couponcenter":__('Get_type couponcenter'),"newpersion":__('Get_type newpersion'),"backend":__('Get_type backend')}, formatter: Table.api.formatter.normal},
                        {field: 'add_time', title: __('Add_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'is_used', title: __('Is_used')},
                        {field: 'use_time', title: __('Use_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'order_sn', title: __('Order_sn'), operate:false},
                        // {field: 'is_new', title: __('Is_new')},
                        // {field: 'use_start_time', title: __('Use_start_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'use_end_time', title: __('Use_end_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
            $("input[name='row[send_obj]']:first").trigger("click");
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                let form = $("form[role=form]")
                Form.api.bindevent(form);

                // 是否限制用户等级
                form.on("click fa.event.typeupdated", "input[name='row[send_obj]']", function (e, ref) {
                    $(".sendobj").addClass("hidden");
                    $(".sendobj.sendobj-" + $(this).val()).removeClass("hidden");
                });

            }
        }
    };
    return Controller;
});