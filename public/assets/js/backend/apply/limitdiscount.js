define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'litestoregoods'], function ($, undefined, Backend, Table, Form, Template, litestoregoods) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'apply/limitdiscount/index',
                    add_url: 'apply/limitdiscount/add',
                    edit_url: 'apply/limitdiscount/edit',
                    del_url: 'apply/limitdiscount/del',
                    multi_url: 'apply/limitdiscount/multi',
                    manage_url: 'apply/limitdiscount/manage',
                    goodslist_url: 'apply/limitdiscount/goodslist',
                    table: 'limitdiscount',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                /*operate  false 没有搜索  RANGE 时间搜索  BETWEEN 区间搜索   LIKE 模糊搜索*/
                columns: [
                    [   {checkbox: true},
                        {field: 'id', title: 'ID', operate: false},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},

                        {
                            field: 'start_time',
                            title: __('Starttime'),
                            formatter: Table.api.formatter.datetime,
                            operate: false
                        },
                        {
                            field: 'end_time',
                            title: __('Endtime'),
                            formatter: Table.api.formatter.datetime,
                            operate: false
                        },
                        // {field: 'upper_num', title: __('Uppernum'), operate: false,},

                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status, //函数 方法
                            searchList: {1: __('Normal'), 0: __('关闭')},
                            sortable: true,
                        },
                        {
                            field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'limit_discount_manage',
                                    text: __('activity list'),
                                    icon: 'fa fa-folder-open-o',
                                    classname: 'btn btn-xs btn-primary',
                                    url: 'apply/limitdiscount/manage'
                                },
                            ],
                            events: Table.api.events.operate, formatter: function (value, row, index) {
                                var that = $.extend({}, this);
                                var table = $(that.table).clone(true);
                                //if (!row.editable)
                                // $(table).data("operate-edit", null);
                                that.table = table;
                                return Table.api.formatter.operate.call(that, value, row, index);
                            }
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
        manage: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'apply/limitdiscount/manage?limit_discount_id=' + Config.limit_discount_id,
                    table: 'limitdiscountgoods',
                    del_url: 'apply/limitdiscount/limit_goods_del',
                    edit_url: 'litestore/litestoregoods/edit/marketing_id/{$row.limit_discount_id}/goods_id/{$row.goods_id}/marketing_type/2',
                    multi_url: 'apply/limitdiscount/edit_upper_num',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                /*operate  false 没有搜索  RANGE 时间搜索  BETWEEN 区间搜索   LIKE 模糊搜索*/
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'id', title: __('IDS'), operate: false},
                        {
                            field: 'image',
                            title: __('Images'),
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {field: 'goods_name', title: __('Goodsname'), operate: 'LIKE'},
                        {field: 'goods_price', title: __('活动价'), operate: false},
                        {field: 'line_price', title: __('Lineprice'), operate: false},

                        {field: 'stock_num', title: __('Stocknum'), operate: false},

                        {field: 'sales', title: __('Salesactual'), operate: false},

                        {field: 'upper_num', title: __('Uppernum'), operate: false,
                            /*formatter: function (value, row, index) {
                                return '<input type="text" class="form-control text-center text-upper_num" data-id="' + row.id + '" value="' + value + '" style="width:50px;margin:0 auto;" />';
                            },
                            events: {
                                "dblclick .text-upper_num": function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    return false;
                                }
                            }*/
                            },
                        {
                            field: 'status',
                            title: __('商品状态'),
                            searchList: {
                                "20": __('已下架'),
                                "10": __('销售中'),
                                "30": __('已结束'),
                            },
                            formatter: Table.api.formatter.status},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                          /*  buttons:[
                                {
                                    name: 'limit',
                                    text: __('限购用户'),
                                    icon: 'fa fa-wrench',
                                    classname: 'btn btn-xs btn-olive btn-dialog',
                                    url: 'Limituser/index',
                                    /!*visible: function (row) {
                                        return  true;
                                    }*!/
                                }],*/
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });
            // 绑定TAB事件
            $('.panel-heading a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var field = $(this).closest("ul").data("field");
                var value = $(this).data("value");
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    params.model_id = value;
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;
            });

            $(document).on("change", ".text-upper_num", function () {
                $(this).data("params", $(this).val());
                Table.api.multi('', [$(this).data("id")], table, this);
                return false;
            });
            // 为表格绑定事件
            Table.api.bindevent(table);

        },
        limit_goods_edit: function () {
            Controller.api.bindevent();
        },
        goodslist: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'apply/limitdiscount/goodslist',
                  //  goods_add_url: 'apply/limitdiscount/goods_add',
                    //edit_url: 'litestore/litestoregoods/edit',
                    table: 'litestoregoods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                sortName: 'goods_sort',
                /*operate  false 没有搜索  RANGE 时间搜索  BETWEEN 区间搜索   LIKE 模糊搜索*/
                columns: [
                    [   {field: 'state', checkbox: true},
                        {field: 'goods_id', title: __('ID'), operate: false},
                        {field: 'image', title: __('Images'), formatter: Table.api.formatter.image, operate: false},

                        {field: 'goods_name', title: __('Goodsname'), operate: 'LIKE'},


                        {field: 'marketing_goods_price', title: __('活动价'), operate: false},


                        // {field: 'line_price', title: __('Lineprice'), operate: false},


                        {field: 'stock_num', title: __('Stocknum'), operate: false},


                        {field: 'sales_actual', title: __('Salesactual'), operate: false},



                        {
                            field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'join',
                                    text: '选取加入',
                                    title: '选取加入',
                                    icon: 'fa fa-gbp',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'litestore/litestoregoods/edit/marketing_id/' + Config.limit_discount_id + '/goods_id/{$row.goods_id}/marketing_type/2'
                                }
                            ],
                            events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                        }

                    ]
                ]
            });

            //添加条件
            $(function (e) {
                var options = table.bootstrapTable('getOptions');
                var queryParams = options.queryParams;
                options.queryParams = function (params) {
                    var params = queryParams(params);
                    var filter = params.filter ? JSON.parse(params.filter) : {};
                    filter['is_marketing'] = 0;
                    filter['is_delete'] = 0;
                    filter['goods_status'] = 10;
                    params.filter = JSON.stringify(filter);
                    return params;
                };

                table.bootstrapTable('refresh', {});
                return false;
            });
            // 为表格绑定事件
            Table.api.bindevent(table);

        },
        goods_add: function () {


            Controller.api.bindevent();

            var specMany = new GoodsSpec({
                container: '.content',
                OutForm: Form
            }, from_specData);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});