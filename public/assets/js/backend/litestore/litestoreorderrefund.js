define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'litestore/litestoreorderrefund/index',
                    // edit_url: 'litestore/litestoreorderrefund/edit',
                    del_url: 'litestore/litestoreorderrefund/del',
                    multi_url: 'litestore/litestoreorderrefund/multi',
                    table: 'litestore_order_refund',
                }
            });

            var table = $("#table");
            Template.helper("Moment", Moment);
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                templateView: true,
                queryParams: function (params) {
                    params.filter = JSON.stringify({'school_id': Config.school_id});
                    params.op = JSON.stringify({'school_id': '='});
                    return params;
                },
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: 'ID', operate: false},
                        {field: 'order_no', title: __('Order_no')},
                        {field: 'refund_no', title: __('退款单号'),operate:"like"},
                        {field: 'liteStoreOrder.mobile', title: __('会员电话'),operate:"like"},

                        {field: 'lite_store_order.pay_price', title: __('订单金额'), operate: false},
                        {field: 'money', title: __('退款金额'), operate: false},
                        {field: 'use_money', title: __('抵扣金额'), operate: false},
                        {field: 'use_qrcode', title: __('抵扣积分'), operate: false},
                        {field: 'lite_store_order.coupon_price', title: __('Coupon_price'), operate: false},
                        {field: 'remark', title: '退款原因', operate: false},
                        {
                            field: 'apply_status',
                            title: '退款状态',
                            searchList: {'1': '申请中', '2': '已通过', '3': '已拒绝', '4': '已取消'},
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'review',
                                    text: '审核',
                                    title: '审核',
                                    classname: 'btn btn-xs btn-warning btn-dialog',
                                    url: 'litestore/litestoreorderrefund/review',
                                    visible: function (row) {
                                        return row.apply_status === 1 ? true : false;
                                    }
                                },
                                {
                                    name: 'detail',
                                    text: '详情',
                                    title: '详情',
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    url: 'litestore/litestoreorderrefund/detail',
                                    visible: function (row) {
                                        return row.apply_status !== 1 ? true : false;
                                    }
                                }
                            ]
                        }
                    ]
                ],
            });

// 为表格绑定事件
            Table.api.bindevent(table);
        },

        review: function () {
            $('.by').removeClass('disabled');
            $('.refuse').removeClass('disabled');

            function submit(id, status, remark) {
                Fast.api.ajax(
                    {url: 'litestore/litestoreorderrefund/review', data: {ids: id, status: status, remark: remark}},
                    function () {
                        parent.Toastr.success('操作成功');
                        Fast.api.close();
                        parent.$('#table').bootstrapTable('refresh');
                    })
            }

            $(document).on('click', '.by,.refuse', function () {
                var status = $(this).data('status');
                var id = $('#id').val();
                if (status == 2) {
                    remark = Layer.prompt({
                        title: '请输入拒绝原因',
                        success: function (layer) {
                            $('input', layer).prop("placeholder", '请输入拒绝原因');
                        }
                    }, function (value) {
                        submit(id, status, value);
                        Layer.close();
                    });
                } else {
                    submit(id, status, '')
                }
            });

            Controller.api.bindevent();
        },

        detail: function () {
            Controller.api.bindevent();
        },

        edit: function () {
            Controller.api.bindevent();
        },

        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
                var imgs = $('#img').val();
                if (imgs) {
                    $('.img-preview').html(Template('img_preview', {item: imgs.split(',')}));
                }
            }
        }
    };
    return Controller;
});