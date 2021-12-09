define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {

                    index_url: 'apply/cutdown/index',
                    add_url: 'apply/cutdown/add',
                    edit_url: 'apply/cutdown/edit',
                    del_url: 'apply/cutdown/del',
                    multi_url: 'apply/cutdown/multi',
                    manage_url: 'apply/cutdown/manage',
                    goodslist_url: 'apply/cutdown/goodslist',
                    table: 'cutdown',
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
                        {field: 'upper_num', title: __('Uppernum'), operate: false},

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

                        // {
                        //     field: 'end_time',
                        //     title: __('Starttimes'),
                        //     visible: false,
                        //     formatter: Table.api.formatter.datetime,
                        //     operate: 'RANGE',
                        //     addclass: 'datetimerange',
                        //
                        // },

                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
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
                                    url: 'apply/cutdown/manage'
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
                    index_url: 'apply/cutdown/manage?cut_down_id=' + Config.cut_down_id,
                    table: 'cutdowngoods',
                    edit_url: 'apply/cutdown/limit_goods_edit',
                    del_url: 'apply/cutdown/limit_goods_del',
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
                        {field: 'id', title: '活动ID', operate: false},
                        {
                            field: 'image',
                            title: __('Images'),
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {field: 'goods_name', title: __('Goodsname'), operate: 'LIKE'},
                        {field: 'key_name', title: __('Keyname'), operate: false},
                        {field: 'goods_price', title: __('Goodsprice'), operate: false},
                        {field: 'discount_price', title: __('Discountprice'), operate: false},
                        {field: 'highest_price', title: __('Highest_price'), operate: false},
                        {field: 'floor_price', title: __('Floor_price'), operate: false},
                        {field: 'stock', title: __('Stock'), operate: false},
                        {field: 'min_price', title: __('Minprice'), operate: false},
                        {field: 'number', title: __('Number'), operate: false},
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
                    index_url: 'apply/cutdown/goodslist',
                    goods_add_url: 'apply/cutdown/goods_add',
                    table: 'litestoregoodsspec',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'goods_spec_id',
                sortName: 'goods_spec_id',
                /*operate  false 没有搜索  RANGE 时间搜索  BETWEEN 区间搜索   LIKE 模糊搜索*/
                columns: [
                    [
                        {field: 'goods_spec_id', title: __('Goodsspecid'), operate: false},
                        {field: 'images', title: __('Images'), formatter: Table.api.formatter.image, operate: false},
                        {
                            field: 'category_id', title: __('分类搜索'), visible: false, searchList: function (column) {
                                return Template('categorytpl', {});
                            }
                        },
                        {field: 'goods_name', title: __('Goodsname'), operate: 'LIKE'},
                        {field: 'goods_price', title: __('Goodsprice'), operate: false},
                        {field: 'stock_num', title: __('Stock_num'), operate: false},
                        {field: 'key_name', title: __('Keyname'), operate: false},
                        {
                            field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'limit_discount_manage',
                                    text: '选取加入',
                                    title: '选取加入',
                                    icon: 'fa fa-gbp',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'apply/cutdown/goods_add/cut_down_id/' + Config.cut_down_id + '/goods_id/{$row.goods_id}'
                                }
                            ],
                            events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                        }

                    ]
                ]
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