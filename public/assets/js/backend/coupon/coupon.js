define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'coupon/coupon/index',
                    add_url: 'coupon/coupon/add',
                    edit_url: 'coupon/coupon/edit',
                    del_url: 'coupon/coupon/del',
                    multi_url: 'coupon/coupon/multi',
                    table: 'coupon',
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
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'title', title: __('Title'), align: 'left',operate:'LIKE'},
                        
                        {field: 'coupon_price', title: __('Couponprice'), align: 'left'},
                        {field: 'condition', title: __('Condition'),operate:false},
                        {field: 'number', title: __('Number'),operate:false},
                        {field: 'createtime', title: __('Createtime'), formatter: Table.api.formatter.datetime,operate:false, sortable: true},
                        {field: 'starttime', title: __('Starttime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true,operate:false},
                        {field: 'endtime', title: __('Endtime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'receive_number', title: __('Receivenumber'),operate:false},
                        {field: 'complete_number', title: __('Completenumber'),operate:false},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.toggle , searchList: {1: __('Normal'), 2: __('Hidden')}},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
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