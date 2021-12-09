define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'litestoregoods'], function ($, undefined, Backend, Table, Form, Template, litestoregoods) {

    var Controller = {
        index: function () {
            $(".btn-add").data("area", ["1000px", "800px"]);
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vip/goods/index',
                    add_url: 'vip/goods/add',
                    edit_url: 'vip/goods/edit',
                    del_url: 'vip/goods/del',
                    multi_url: 'vip/goods/multi',
                    table: 'litestore_goods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'goods_id',
                sortName: 'goods_sort',
                commonSearch: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'goods_id', title: __('Goods_id'), operate: false},
                        {field: 'goods_name', title: __('Goods_name'), operate: false},
                        {
                            field: 'vip_level',
                            title: 'VIP类型',
                            searchList: {'1': '普通VIP', '2': '尊享VIP'},
                            formatter: Table.api.formatter.normal,
                            operate: false
                        },
                        {field: 'is_recommend', title: '推荐', operate: false, formatter: Table.api.formatter.toggle},
                        {
                            field: 'goods_status',
                            title: __('Goods_status'),
                            searchList: {'10': '出售中', '20': '已售完', '30': '仓库中'},
                            custom: {'10': 'success', '20': 'danger', '30': 'info'},
                            formatter: Table.api.formatter.status,
                            operate: false
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('load-success.bs.table', function (data) {
                $(".btn-editone").data("area", ["1000px", "800px"]);
            });

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
            $('.send_num').trigger('change');
        },
        select: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'litestore/litestoregoods/select',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'goods_id',
                sortName: 'goods_sort',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'goods_id', title: __('Goods_id')},
                        {field: 'goods_name', title: __('Goods_name'), operate: 'LIKE',},
                        {
                            field: 'category_id', visible: false, title: __('分类搜索'), searchList: function (column) {
                                return Template('categorytpl', {});
                            }
                        },
                        {
                            field: 'operate', title: __('Operate'), events: {
                                'click .btn-chooseone': function (e, value, row, index) {
                                    var multiple = Backend.api.query('multiple');
                                    multiple = multiple == 'true' ? true : false;
                                    Fast.api.close(row);
                                },
                            }, formatter: function (value, row) {
                                return '<a href="javascript:;" class="btn btn-danger btn-chooseone btn-xs"><i class="fa fa-check"></i> ' + __('Choose') + '</a>';
                            }
                        }

                    ]
                ]
            });


            // 选中多个
            $(document).on("click", ".btn-choose-multi", function () {
                var urlArr = new Array();
                $.each(table.bootstrapTable("getAllSelections"), function (i, j) {
                    urlArr.push(j.url);
                });
                var multiple = Backend.api.query('multiple');
                multiple = multiple == 'true' ? true : false;
                Fast.api.close({url: urlArr.join(","), multiple: true});
            });


            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
                $('.send_num').change(function () {
                    if (parseInt($(this).val()) > 1) {
                        $('.time_interval').show()
                    } else {
                        $('.time_interval').hide();
                        $('.time_interval input').val(0);
                    }
                })
            }
        }
    };
    return Controller;
});