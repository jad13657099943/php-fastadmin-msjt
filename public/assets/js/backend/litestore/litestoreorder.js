define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'litestore/litestoreorder/index',
                    add_url: 'litestore/litestoreorder/add',
                    del_url: 'litestore/litestoreorder/del',
                    multi_url: 'litestore/litestoreorder/multi',
                    // qrcode_url: 'litestore/litestoreorder/qrcode',
                    table: 'litestore_order',
                }
            });

            var table = $("#table");
            Template.helper("Moment", Moment);


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                // showExport: true,
                // exportDataType: 'selected',
                templateView: true,

                columns: [
                    [
                        {checkbox: true},
                        // {field: 'id', title: __('Id')},
                        {field: 'order_no', title: __('Order_no')},

                        {
                            field: 'is_status',
                            title: '订单类型',
                            searchList: {'1': '配送订单', '2': '自提订单'},
                            formatter: Table.api.formatter.normal,
                            operate: "="
                        },
                        {field: 'total_price', title: __('Total_price'), operate: false},
                        {field: 'express_price', title: __('Express_price'), operate: false},
                        {field: 'pay_price', title: __('Pay_price'), operate: false},
                        /* {
                             field: 'pay_time',
                             title: __('Pay_time'),
                             operate: 'RANGE',
                             addclass: 'datetimerange',
                             formatter: Table.api.formatter.datetime
                         },
                         {
                             field: 'freight_time',
                             title: __('Freight_time'),
                             operate: false,
                             addclass: 'datetimerange',
                             formatter: Table.api.formatter.datetime
                         },
                         {
                             field: 'receipt_time',
                             title: __('Receipt_time'),
                             operate: false,
                             addclass: 'datetimerange',
                             formatter: Table.api.formatter.datetime
                         },*/
                        {
                            field: 'order_status',
                            title: __('Order_status'),
                            searchList: {
                                "0": __('Order_status 0'),
                                "10": __('Order_status 10'),
                                "20": __('Order_status 20'),
                                "30": __('Order_status 30'),
                                "40": __('Order_status 40'),
                                "50": __('Order_status 50'),
                                "60": __('Order_status 60'),
                            },
                            operate: false,
                            formatter: Controller.api.status_formatter
                        },
                        {field: 'mobile', title: __("会员电话"), operate: "like", visible: false},
                        {field: 'address.name|consignee', title: __("收货人"), operate: "like", visible: false},
                        {field: 'address.phone|reserved_telephone', title: __("联系电话"), operate: "like", visible: false},//reserved_telephone
                        {
                            field: 'reserved_telephone', title: __("联系电话"), operate: false,
                            formatter: function (value, row, index) {
                                return row.is_status == 1 ? row.address.phone : row.reserved_telephone;
                            }
                        },

                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate', title: __('Operate'), table: table, buttons: [
                                {
                                    name: 'detail',
                                    text: __('view'),
                                    icon: 'fa fa-eye',
                                    classname: 'btn btn-xs btn-green btn-dialog chakan',
                                    url: 'litestore/litestoreorder/detail',
                                    visible: function (row) {
                                        return row.order_status != 20 ? true : false;
                                    }
                                },
                                {
                                    name: 'detail',
                                    text: __('delivery'),
                                    icon: 'fa fa-eye',
                                    classname: 'btn btn-xs btn-green btn-dialog chakan',
                                    url: 'litestore/litestoreorder/detail',
                                    visible: function (row) {
                                        return row.order_status == 20 ? true : false;
                                    }
                                },


                            ], events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]/*,
                onLoadSuccess: function (data) {
                    Backend.api.sidebar({
                        'litestore/litestoreorder': data.total_number
                    });
                }*/
            });


            // 绑定TAB事件
            $('.panel-heading a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var that = $(this);
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;

                var field = 'order_status';
                var value = $(this).data("order_status");
                var is_status_ = $(this).data("is_status_");
                var is_status = $(this).data("is_status");
                var queryParams = options.queryParams;
                options.queryParams = function (params) {
                    var params = queryParams(params);
                    var filter = params.filter ? JSON.parse(params.filter) : {};
                    console.log(is_status)
                    if (value !== '') {
                        if (value == '70') {
                            filter["is_status"] = 2;
                            filter["order_status"] = 30;
                        }else if(value=='30'){
                            filter["order_status"] = 30;
                            filter["is_status"] = 1;
                        }else{
                            filter={};
                            filter[field] = value;
                        }
                    }
                    /*     console.log(is_status)
                         if (is_status !== '') {
                             filter['is_status'] = is_status;
                         }*/

                    /*if (is_status_ !== '') {
                        filter['is_status'] = is_status_;
                    }*/
                    params.filter = JSON.stringify(filter);
                    return params;
                };

                table.bootstrapTable('refresh', {});
                return false;
            });


            //*************************** 自定义export开始
            var submitForm = function (ids, layero) {
                var options = table.bootstrapTable('getOptions');

                var columns = [];
                $.each(options.columns[0], function (i, j) {
                    if (j.field && !j.checkbox && j.visible && j.field != 'operate') {
                        columns.push(j.field);
                    }
                });
                var search = options.queryParams({});
                $("input[name=search]", layero).val(options.searchText);
                $("input[name=ids]", layero).val(ids);
                $("input[name=filter]", layero).val(search.filter);
                $("input[name=op]", layero).val(search.op);
                $("input[name=columns]", layero).val(columns.join(','));
                $("form", layero).submit();
            };

            $(document).on("click", ".btn-export", function () {
                var ids = Table.api.selectedids(table);
                var page = table.bootstrapTable('getData');
                var all = table.bootstrapTable('getOptions').totalRows;

                Layer.confirm("请选择导出的选项" +
                    "<form action='" + Fast.api.fixurl("litestore/litestoreorder/out") + "' method='post' target='_blank'>" +
                    "<input type='hidden' name='ids' value='' />" +
                    "<input type='hidden' name='filter' >" +
                    "<input type='hidden' name='op'>" +
                    "<input type='hidden' name='search'>" +
                    "<input type='hidden' name='columns'>" +
                    "</form>", {
                    title: '导出数据',
                    btn: ["选中项(" + ids.length + "条)", "本页(" + page.length + "条)", "全部(" + all + "条)"],
                    success: function (layero, index) {
                        $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
                    }
                    , yes: function (index, layero) {
                        submitForm(ids.join(","), layero);
                        return false;
                    }
                    ,
                    btn2: function (index, layero) {
                        var ids = [];
                        $.each(page, function (i, j) {
                            ids.push(j.id);
                        });
                        submitForm(ids.join(","), layero);
                        return false;
                    }
                    ,
                    btn3: function (index, layero) {
                        submitForm("all", layero);
                        return false;
                    }
                });

                //关闭弹窗  刷新页面
                $(document).on("click", ".layui-layer-btn0", function () {
                    Layer.closeAll();
                    $(".btn-refresh").trigger("click");
                });
            });

            //*************************** 自定义export结束
            //Controller.api.bindevent();


            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('load-success.bs.table', function (data) {
                $('.chakan').data("area", ["1000px", "800px"]);
            });
        },


        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        detail: function () {
            $("#send").on('click', function () {
                var sn = $("#c-virtual_sn").val();
                var name = $("#c-virtual_name").val();
                if (sn == '' || name == '') {
                    layer.msg("请填写正确的快递信息");
                    return false;
                }
                $("#send-form").attr("action", "litestore/litestoreorder/detail").submit();
            });
            $(".btn-refresh").trigger("click");
            Controller.api.bindevent();
        },
        qrcode: function () {
            $("#send").on('click', function () {
                var qrcode = $("#c-qrcode.qrcode").val();
                if (qrcode == '') {
                    layer.msg("提货码不能为空");
                    return false;
                }
                $("#send-form").attr("action", "litestore/litestoreorder/qrcode").submit();
            });
            $(".btn-refresh").trigger("click");
            Controller.api.bindevent();
        },

        refund: function () {
            $("#send").on('click', function () {
                var name = $("#order_goods_id").val();
                if (name == '') {
                    layer.msg("请勾选退款商品");
                    return false;
                }

                var name = $("#c-remark").val();
                if (name == '') {
                    layer.msg("请填写退款原因");
                    return false;
                }
                $("#send-form").attr("action", "litestore/litestoreorder/refund").submit();
            });
            $(".btn-refresh").trigger("click");
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            status_formatter: function (value, row, index) {
                var colorArr = ["success", "gray", "blue", "primary", "danger", "warning", "info", "red", "yellow", "aqua", "navy", "teal", "olive", "lime", "fuchsia", "purple", "maroon"];
                var custom = {};
                if (typeof this.custom !== 'undefined') {
                    custom = $.extend(custom, this.custom);
                }
                value = value === null ? '' : value.toString();
                var keys = typeof this.searchList === 'object' ? Object.keys(this.searchList) : [];
                var index = keys.indexOf(value);
                var color = value && typeof custom[value] !== 'undefined' ? custom[value] : null;
                if (index > -1) {
                    var display = row.order_status != 30 || row.is_status == 1 ? this.searchList[value] : '待自取';
                    //var display = row.order_status == 50 && row.is_status == 1 ? this.searchList[value] : '待自取';
                }
                var icon = "fa fa-circle";
                if (!color) {
                    color = index > -1 && typeof colorArr[index] !== 'undefined' ? colorArr[index] : 'primary';
                }
                if (!display) {
                    display = __(value.charAt(0).toUpperCase() + value.slice(1));
                }
                var html = '<span class="text-' + color + '">' + (icon ? '<i class="' + icon + '"></i> ' : '') + display + '</span>';
                if (this.operate != false) {
                    html = '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + __('Click to search %s', display) + '" data-field="' + this.field + '" data-value="' + value + '">' + html + '</a>';
                }
                return html;
            },
        }
    };
    return Controller;
});