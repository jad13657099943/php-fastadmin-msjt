define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
            index: function () {
                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        index_url: 'litestore/comboorder/index',
                        add_url: 'litestore/comboorder/add',
                        //   del_url: 'litestore/comboorder/del',
                        multi_url: 'litestore/comboorder/multi',
                        table: 'comboorder',
                    }
                });

                var table = $("#table");
                var time = parseInt(new Date().getTime() / 1000);

                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    sortName: 'order_status asc,ship_time desc',
                    columns: [
                        [
                            {checkbox: true},
                            /*{field: 'id', title: __('Id')},*/
                            {field: 'order_no', title: __('Order_no')},
                            {field: 'mobile', title: __('会员电话'), operate: "like", visible: false},
                            {field: 'mobile', title: __('会员电话'), operate: false},
                            /* {field: 'total_price', title: __('Total_price'), operate: false},*/
                            // {field: 'express_price', title: __('Express_price'), operate: false},
                            {field: 'pay_price', title: '实付金额', operate: false},
                            /*      {
                                      field: 'pay_time',
                                      title: __('Pay_time'),
                                      operate: 'RANGE',
                                      addclass: 'datetimerange',
                                      formatter: Table.api.formatter.datetime,
                                      operate: false,
                                  },*/
                            {
                                field: 'type',
                                title: '套餐类型',
                                searchList: {'1': '普通VIP', '2': '尊享VIP'},
                                formatter: Table.api.formatter.normal,
                            },
                            {
                                field: 'is_status',
                                title: '配送方式',
                                searchList: {'1': '配送', '2': '自提'},
                                formatter: Table.api.formatter.normal,
                            },
                            {field: 'username', title: '代理商', operate: false},
                            {
                                field: 'consignee', title: __('收货人'), operate: false,
                                formatter: function (value, row, index) {
                                    return row.is_status == 1 ? row.name : row.consignee;
                                }
                            },
                            {field: 'address.name|consignee', title: __("收货人"), operate: "like", visible: false},
                            {field: 'address.phone|reserved_telephone', title: __("联系电话"), operate: "like", visible: false},//reserved_telephone
                            {
                                field: 'reserved_telephone', title: __("联系电话"), operate: false,
                                formatter: function (value, row, index) {
                                    return row.is_status == 1 ? row.phone : row.reserved_telephone;
                                }
                            },
                            {
                                field: 'site',
                                title: __("收货地址"),
                                operate: false,
                                formatter: function (value, row, index) {
                                    return row.is_status == 1 ? row.site : '/';
                                }
                            },
                            {
                                field: 'createtime',
                                title: '下单时间',
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime,
                            },
                            {
                                field: 'operate', title: __('Operate'), table: table, buttons: [
                                    {
                                        name: 'ship',
                                        text: __('立即发货'),
                                        // icon: 'fa fa-wrench',
                                        classname: 'btn btn-xs btn-info btn-dialog',
                                        url: 'litestore/comboorder/ship',
                                        visible: function (row) {
                                            return time + 3 * 86400 > row.ship_time && row.order_status == 20 && row.is_status == 1 ? true : false;
                                        }
                                    },
                                    {
                                        name: 'detail',
                                        text: '发货记录',
                                        classname: 'btn btn-xs btn-success btn-dialog',
                                        // icon: 'fa fa-pencil',
                                        url: 'litestore/comboorder/detail',
                                        visible: function (row) {
                                            return row.is_status == 1 ? true : false;
                                        }
                                    },

                                    {
                                        name: 'write_off',
                                        text: '核销记录',
                                        classname: 'btn btn-xs btn-warning btn-dialog',
                                        // icon: 'fa fa-pencil',
                                        url: 'litestore/comboorder/write_off',
                                        visible: function (row) {
                                            return row.is_status == 2 ? true : false;
                                        }
                                    }, {
                                        name: 'refund',
                                        text: '退款',
                                        classname: 'btn btn-xs btn-red  btn-click',
                                        click: function (data, row) {
                                            var id = row.id;

                                            remark = Layer.prompt({
                                                title: '请输入退款原因',
                                                success: function (layer) {
                                                    $('input', layer).prop("placeholder", '请输入退款原因');
                                                }
                                            }, function (value) {
                                                Layer.closeAll()
                                                Fast.api.ajax(
                                                    {url: 'litestore/comboorder/refund', data: {ids: id, remark: value}},
                                                    function () {
                                                        parent.Toastr.success('操作成功');
                                                        Layer.close();
                                                        $('#table').bootstrapTable('refresh');
                                                        return false;
                                                    })
                                            });
                                        },
                                        visible: function (row) {
                                            return row.current_frequency == 0 ? true : false;
                                        },
                                    },

                                ], events: Table.api.events.operate, formatter: Table.api.formatter.operate
                            }
                        ]
                    ]/*,
                onLoadSuccess: function (data) {
                    Backend.api.sidebar({
                        'litestore/litestoreorder': data.total_number
                    });
                }*/
                });


                //退款弹出框
                /*  $(document).on('click', '.btn-click', function () {
                      var id = Config.ids;
                      console.log(Config)
                      remark = Layer.prompt({
                          title: '请输入退款原因',
                          success: function (layer) {
                              $('input', layer).prop("placeholder", '请输入退款原因');
                          }
                      }, function (value) {
                          Fast.api.ajax(
                              {url: 'litestore/comboorder/refund', data: {ids: id, remark: remark}},
                              function () {
                                  parent.Toastr.success('操作成功');
                                  Fast.api.close();
                                  parent.$('#table').bootstrapTable('refresh');
                              })

                          Layer.close();
                      });

                  });*/


                // 绑定TAB事件
                $('.panel-heading a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    var that = $(this);
                    var options = table.bootstrapTable('getOptions');
                    options.pageNumber = 1;

                    var field = 'order_status';
                    var value = $(this).data("order_status");
                    var queryParams = options.queryParams;
                    options.queryParams = function (params) {
                        var params = queryParams(params);
                        var filter = params.filter ? JSON.parse(params.filter) : {};
                        if (value !== '') {
                            filter[field] = value;
                        }
                        params.filter = JSON.stringify(filter);
                        return params;
                    };

                    table.bootstrapTable('refresh', {});
                    return false;
                });


                //*************************** 自定义export开始
                var submitForm = function (ids, layero) {
                    var options = table.bootstrapTable('getOptions');
                    console.log(options);
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
                    console.log(ids, page, all);
                    Layer.confirm("请选择导出的选项<form action='" + Fast.api.fixurl("litestore/comboorder/out") + "' method='post' target='_blank'><input type='hidden' name='ids' value='' /><input type='hidden' name='filter' ><input type='hidden' name='op'><input type='hidden' name='search'><input type='hidden' name='columns'></form>", {
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
            ship: function () {
                Controller.api.bindevent();
            },
            detail: function () {
                // 初始化表格参数配置
                Table.api.init();
                var table = $("#table");
                // 初始化表格
                table.bootstrapTable({
                    url: 'litestore/comboorder/detail/ids/' + ids,
                    pk: 'id',
                    sortName: 'create_time',
                    order: 'desc',
                    commonSearch: false,
                    columns: [
                        [
                            {field: 'goods_info', title: '商品内容'},
                            {field: 'express_company', title: '快递公司', operate: false},
                            {field: 'express_no', title: '快递单号', operate: false},
                            {
                                field: 'receipt_status', title: '状态', formatter: function (row) {
                                    return row == 10 ? '待签收' : '已签收';
                                }
                            },
                            {field: 'create_time', title: '发货时间', formatter: Table.api.formatter.datetime},
                        ]
                    ],
                });
                // 为表格绑定事件
                Table.api.bindevent(table);
            },
            write_off: function () {
                // 初始化表格参数配置
                Table.api.init();
                var table = $("#table");
                // 初始化表格
                table.bootstrapTable({
                    url: 'litestore/comboorder/write_off/ids/' + ids,
                    pk: 'id',
                    sortName: 'create_time',
                    order: 'desc',
                    commonSearch: false,
                    columns: [
                        [
                            {field: 'remark', title: '核销清单'},
                            {field: 'nickname', title: '核销人', operate: false},
                            {field: 'create_time', title: '核销时间', formatter: Table.api.formatter.datetime},
                        ]
                    ],
                });
                // 为表格绑定事件
                Table.api.bindevent(table);
            },

            api: {
                bindevent: function () {
                    Form.api.bindevent($("form[role=form]"));
                }
                ,
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
                    var display = index > -1 ? this.searchList[value] : null;
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
                }
                ,
            }
        }
    ;
    return Controller;
});