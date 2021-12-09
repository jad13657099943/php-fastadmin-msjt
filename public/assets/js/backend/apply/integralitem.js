define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'apply/integralitem/index',
                    add_url: 'apply/integralitem/add',
                    edit_url: 'apply/integralitem/edit',
                    del_url: 'apply/integralitem/del',
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
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID',operate:false},
                        {field: 'title', title: __('Title'),operate:'LIKE'},
                        {field: 'img', title: __('Img'), formatter: Table.api.formatter.images},
                        {field: 'integral', title: __('Integral')},
                        {field: 'inventory', title: __('Inventory')},
                        {field: 'sales', title: __('Sales')},
                        {field: 'ordid', title: __('Ordid')},
                        {field: 'status',title: __('Status'),formatter:function(value){
                                if (value == 1) {
                                    return '上架';
                                } else if (value == 0) {
                                    return '下架';
                                }
                            },operate:false},
                        {field: 'add_time', title: __('Addtime'), formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}

                    ]
                ]
            });



            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            // 切换基本信息/产品参数
            $('.tab').click(function (e) {
                let index = $(this).index()
                $('.tab').eq(index).addClass('active').siblings().removeClass('active')
                $('.tabletabs').hide()
                $('.tabletabs').eq(index).show()
            });
            Controller.api.bindevent();
        },
        edit: function () {
            $('.tab').click(function (e) {
                let index = $(this).index()
                $('.tab').eq(index).addClass('active').siblings().removeClass('active')
                $('.tabletabs').hide()
                $('.tabletabs').eq(index).show()
            });
            Controller.api.bindevent();
        },
        manage: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'apply/limitdiscount/manage?limit_discount_id='+Config.limit_discount_id,
                    table: 'limitdiscountgoods',
                    edit_url: 'apply/limitdiscount/limit_goods_edit',
                    del_url: 'apply/limitdiscount/limit_goods_del',
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
                        {field: 'id', title: 'ID',operate:false},
                        {field: 'goods_name', title: __('Goodsname'), operate: 'LIKE'},
                        {field: 'goods_price', title: __('Goodsprice'),operate:false},
                        {field: 'discount_price', title: __('Discountprice'),operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}


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
                    index_url: 'apply/limitdiscount/goodslist',
                    goods_add_url: 'apply/limitdiscount/goods_add',
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
                        {field: 'goods_spec_id', title:__('Goodsspecid'),operate:false},
                        {field: 'images', title: __('Images'), formatter: Table.api.formatter.image,operate:false},
                        {field: 'category_id', title: __('分类搜索'),visible:false, searchList: function (column) {
                                return Template('categorytpl', {});
                            }},
                        {field: 'goods_name', title: __('Goodsname'),operate:'LIKE'},
                        {field: 'key_name', title: __('Keyname'),operate:false},
                        {field: 'goods_price', title: __('Goodsprice'),operate:false},
                        {
                            field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {name: 'limit_discount_manage', text: '选取加入', title: '选取加入', icon: 'fa fa-gbp', classname: 'btn btn-xs btn-primary btn-dialog', url: 'apply/limitdiscount/goods_add/limit_discount_id/'+Config.limit_discount_id+'/goods_id/{$row.goods_id}'}
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