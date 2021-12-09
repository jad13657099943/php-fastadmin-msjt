define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'litestore/useragentapply/index',
                    add_url: 'litestore/useragentapply/add',
                    edit_url: 'litestore/useragentapply/edit',
                    del_url: 'litestore/useragentapply/del',
                    multi_url: 'litestore/useragentapply/multi',
                    table: 'user_agent_apply',
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
                        {field: 'id', title: __('Id'),operate: false},
                        // {field: 'uid', title: __('Uid'),operate: false},
                        // {field: 'identity_front', title: __('Identity_front'),operate: false,formatter: Table.api.formatter.images},
                        // {field: 'identity_reverse', title: __('Identity_reverse'),operate: false,formatter: Table.api.formatter.image},
                        // {field: 'status',
                        //     title: __('Status'),
                        //     searchList: {"0":__('审核中'),"1":__('已通过'),"2":__('已拒绝')},
                        //     operate: false,
                        //     formatter: Table.api.formatter.status
                        // },
                        //
                        {field: 'store_name', title: __('Store_name'),operate: 'like'},
                        // {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'msg', title: __('Msg'),operate: false},
                        // {field: 'review_time', title: __('Review_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'username', title: __('Username'),operate: false},
                        {field: 'mobile', title: __('Mobile'),operate: false},
                        // {field: 'latitude', title: __('Latitude'),operate: false},
                        // {field: 'longitude', title: __('Longitude'),operate: false},
                        {field: 'address', title: __('Address'),operate: false},
                        // {field: 'business_license', title: __('Business_license'),operate: false,formatter: Table.api.formatter.image},
                        // {field: 'store_status', title: __('Store_status'),
                        //     searchList: {"0":__('待审核'),"1":__('审核通过'),"2":__('审核失败'),"3":__('后台退款')},
                        //     operate: false,
                        //     formatter: Table.api.formatter.status},
                        // {field: 'id_card', title: __('Id_card'),operate: false},
                        // {field: 'business_hours', title: __('Business_hours'),operate: false},
                        // {field: 'site', title: __('Site'),operate: false},
                        {field: 'opening_hours', title: __('Opening_hours'),operate: false},
                        {field: 'closing_hours', title: __('Closing_hours'),operate: false},
                        // {field: 'holding_id_card', title: __('Holding_id_card'),operate: false,formatter: Table.api.formatter.image},
                        // {field: 'apply_money', title: __('Apply_money'), operate:false},
                        // {field: 'pay_money', title: __('Pay_money'), operate:false},
                        // {field: 'order_no', title: __('Order_no'),operate: false},
                        // {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'is_message', title: __('Is_message'),operate: false},
                         {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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