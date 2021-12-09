define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {

                    index_url: 'apply/groupbuy/index',
                    add_url: 'apply/groupbuy/add',
                    edit_url: 'apply/groupbuy/edit',
                    del_url: 'apply/groupbuy/del',
                    multi_url: 'apply/groupbuy/multi',
                    manage_url: 'apply/groupbuy/manage',
                    goodslist_url: 'apply/groupbuy/goodslist',
                    table: 'groupbuy',
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
                        {checkbox: true},
                        {field: 'id', title: 'ID', operate: false},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'group_num', title: __('Groupnum'), operate: false},
                        {field: 'hour', title: __('Hour'), operate: false},
                        {field: 'upper_num', title: __('Uppernum'),  operate: false},


                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status, //函数 方法
                            searchList: {1: __('Normal'), 0: __('Hidden')},
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
                                    url: 'apply/groupbuy/manage'
                                }
                            ],
                            events: Table.api.events.operate, formatter: function (value, row, index) {
                                var that = $.extend({}, this);
                                var table = $(that.table).clone(true);
                                // if (!row.editable)
                                //     $(table).data("operate-edit", null);
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
                    index_url: 'apply/groupbuy/manage?groupbuy_id=' + Config.groupbuy_id,
                    table: 'groupbuygoods',
                    del_url: 'apply/groupbuy/limit_goods_del',
                    edit_url: 'litestore/litestoregoods/edit/marketing_id/{$row.groupbuy_id}/goods_id/{$row.goods_id}/marketing_type/1',
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
                            title: __('Goodsimage'),
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {field: 'goods_name', title: __('Goodsname'), operate: 'LIKE'},
                        {field: 'goods_price', title: __('Group_price'), operate: false},
                        {field: 'stock_num', title: __('Stocknum'), operate: false},
                        {field: 'upper_num', title: __('Uppernum'), operate: false},
                        {field: 'group_num', title: __('Groupnum'), operate: false},
                        {field: 'group_nums', title: __('Groupnums'), operate: false},
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
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
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
                    index_url: 'apply/groupbuy/goodslist',
                   /* goods_add_url: 'apply/groupbuy/goods_add',*/
                    /*edit_url: 'litestore/litestoregoods/edit',*/
                    table: 'litestoregoodsspec',

                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                sortName: 'goods_sort',
                /*operate  false 没有搜索  RANGE 时间搜索  BETWEEN 区间搜索   LIKE 模糊搜索*/
                columns: [
                    [
                        {field: 'goods_id', title: __('Goodsid'), operate: false},
                        {field: 'image', title: __('Goodsimage'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'goods_name', title: __('Goodsname'), operate: 'LIKE'},

                        {field: 'goods_price', title: __('Goodsprice'), operate: false},
                        {field: 'stock_num', title: __('Stock_num'), operate: false},

                        {
                            field: 'operate', title: __('Operate'), table: table,
                            buttons: [

                                {
                                    name: 'join',
                                    text: '选取加入',
                                    title: '选取加入',
                                    icon: 'fa fa-gbp',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'litestore/litestoregoods/edit/marketing_id/' + Config.groupbuy_id + '/goods_id/{$row.goods_id}/marketing_type/1'
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
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});