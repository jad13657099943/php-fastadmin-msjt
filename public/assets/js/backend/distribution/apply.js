define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'distribution/apply/index',
                    by_url: 'distribution/apply/by',
                    refuse_url: 'distribution/apply/refuse',
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
                        {
                            checkbox: true, formatter: function (value, row) {
                                this.checkboxEnabled = row.store_status == 0 ? true : false;
                            }
                        },
                       /* {field: 'id', title: __('Id'), operate: false},*/
                        {field: 'username', title: '姓名', operate: 'like'},
                        {field: 'mobile', title: '手机号'},
                        // {field: 'pay_money', title: '支付金额', operate: false},
                        // {field: 'uid', title: __('Uid')},
                      /*  {
                            field: 'identity_front',
                            title: '身份证正、反面',
                            formatter: Table.api.formatter.images,
                            operate: false
                        },
                        {
                            field: 'identity_reverse',
                            title: '手持身份证正面',
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {field: 'id_card', title: '身份证号', operate: false},
                        {field: 'store_name', title: '店铺名称'},*/
                        // {field: 'site', title: '省市区', operate: false},
                        {field: 'address', title: '详细地址', operate: "like"},
                        {
                            field: 'store_status',
                            title: __('Status'),
                            searchList: {'0': '待审核', '1': '已通过', '2': "已拒绝",'3':'已退款'},
                            formatter: Table.api.formatter.status,
                            operate: false,
                        },
                        {
                            field: 'create_time',
                            title: __('加入时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'by',
                                    text: '通过',
                                    title: '通过',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-check',
                                    confirm: '你确定通过审核?',
                                    url: $.fn.bootstrapTable.defaults.extend.by_url,
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    visible: function (row) {
                                        return row.store_status == 0 ? true : false;
                                    }
                                },
                                {
                                    name: 'refuse',
                                    text: '拒绝',
                                    title: '拒绝',
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    icon: 'fa fa-close',
                                    url: $.fn.bootstrapTable.defaults.extend.refuse_url,
                                    visible: function (row) {
                                        return row.store_status == 0 ? true : false;
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        }
                    ]
                ]
            });

            $(document).on('click', '.btn-by', function () {
                var ids = Table.api.selectedids(table);
                var url = $(this).data('url');
                Layer.confirm($(this).data('confirm'), {title: '提示'}, function (index) {
                    Fast.api.ajax({
                        url: url,
                        data: {ids: ids.join(",")}
                    }, function () {
                        $(".btn-refresh").trigger("click");
                    });
                    Layer.close(index);
                });
            });

            $(document).on('click', '.btn-refuse', function () {
                var that = this;
                //循环弹出多个编辑框
                $.each(table.bootstrapTable('getSelections'), function (index, row) {
                    Fast.api.open($(that).data('url') + '/ids/' + row.id, '拒绝');
                });
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
        refuse: function () {
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