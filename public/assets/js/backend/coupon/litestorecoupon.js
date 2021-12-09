define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'coupon/litestorecoupon/index',
                    add_url: 'coupon/litestorecoupon/add',
                    edit_url: 'coupon/litestorecoupon/edit',
                    del_url: 'coupon/litestorecoupon/del',
                    multi_url: 'coupon/litestorecoupon/multi',
                    dragsort_url: false,
                    table: 'litestore_coupon',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'id', title: __('Id'), operate:false, visible:false},
                        // {field: 'category_id', title: __('Category_came'), addClass: "selectpage", extend: "data-source='coupon/couponcategory/index' data-field='name'", visible: false},
                        {field: 'category.name', title: __('Category_came'), operate: false},
                        {field: 'name', title: __('Name')},
                        // {field: 'icon_image', title: __('Icon_image'), formatter: Table.api.formatter.image, operate:false},
                        {field: 'enough', title: __('Enough'), operate: 'BETWEEN', operate: false},
                        // {field: 'limit_type', title: __('Limit_type'), searchList: {"timedays":__('Timedays'),"timelimit":__('Timelimit')}, formatter: Table.api.formatter.normal, operate: false, visible: false},
                        // {field: 'timedays', title: __('Timedays'), operate:false, operate: false, visible: false},
                        // {field: 'receive_start_time', title: __('Receive_start_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, operate: false, visible: false},
                        // {field: 'receive_end_time', title: __('Receive_end_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, operate: false, visible: false},
                        // {field: 'use_start_time', title: __('Use_start_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, operate: false, visible: false},
                        // {field: 'use_end_time', title: __('Use_end_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime, operate: false, visible: false},
                        // {field: 'coupon_type', title: __('Coupon_type'), searchList: {"deduct":__('Deduct'),"discount":__('Discount')}, formatter: Table.api.formatter.normal, operate: false, visible: false},
                        // {field: 'deduct', title: __('Deduct'), operate:'BETWEEN', operate: false, visible: false},
                        // {field: 'discount', title: __('Discount'), operate:'BETWEEN', operate: false, visible: false},
                        {field: 'discount_text', title: __('优惠'), operate: false},
                        {field: 'total', title: __('Total'), operate: false},
                        // {field: 'get_type', title: __('Get_type'), searchList: {"1":__('Get_type 1'),"0":__('Get_type 0')}, formatter: Table.api.formatter.normal, operate: false, visible: false},
                        // {field: 'get_max', title: __('Get_max'), operate: false, visible: false},
                        // {field: 'is_limit_level', title: __('Is_limit_level'), searchList: {"0":__('Is_limit_level 0'),"1":__('Is_limit_level 1')}, formatter: Table.api.formatter.normal, operate: false, visible: false},
                        // {field: 'user_level_data', title: __('User_level_data'), searchList: {"":__('')}, operate:'FIND_IN_SET', formatter: Table.api.formatter.label, operate: false, visible: false},
                        // {field: 'limit_goods_category', title: __('Limit_goods_category'), searchList: {"0":__('Limit_goods_category 0'),"1":__('Limit_goods_category 1')}, formatter: Table.api.formatter.normal, operate: false, visible: false},
                        // {field: 'litestore_category_ids', title: __('Litestore_category_ids'), operate: false, visible: false},
                        // {field: 'limit_goods', title: __('Limit_goods'), searchList: {"0":__('Limit_goods 0'),"1":__('Limit_goods 1')}, formatter: Table.api.formatter.normal, operate: false, visible: false},
                        // {field: 'litestore_goods_ids', title: __('Litestore_goods_ids'), operate: false, visible: false},
                        {field: 'receive_num', title: __('Receive_num'), operate: false},
                        {field: 'use_num', title: __('Use_num'), operate: false},
                        {field: 'remainder_num', title: __('Remainder_num'), operate: false},
                        {field: 'weigh', title: __('Weigh'), operate: false},
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
        add: function () {
            Controller.api.bindevent();
            $("input[name='row[get_type]']:last").trigger("click");
            $("input[name='row[is_limit_level]']:first").trigger("click");
            $("input[name='row[limit_goods_category]']:first").trigger("click");
            $("input[name='row[limit_goods]']:first").trigger("click");
        },
        edit: function () {
            Controller.api.bindevent();
            $("input[name='row[is_index]']:checked").trigger('click');
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));

                $("form[role=form]").validator({
                    rule: {
                        user_level_data_rule: function (element, params) {
                            console.log(element)
                            console.log(params)
                        }
                    },
                    fields: {
                        'row[name]': 'required',
                        'row[category_id]': 'required',
                        'row[use_time_range]': 'required(.row-limit_type-timelimit:checked)',
                        'row[deduct]': 'required(.row-coupon_type-deduct:checked);range(0.01~)',
                        'row[discount]': 'required(.row-coupon_type-discount:checked);range(1.0~10.0)',
                    }
                })

                // 是否加入领券中心
                $(document).on("click fa.event.typeupdated", "input[name='row[get_type]']", function (e, ref) {
                    $(".gettype").addClass("hidden");
                    $(".gettype.gettype-" + $(this).val()).removeClass("hidden");
                });

                // 是否限制用户等级
                $(document).on("click fa.event.typeupdated", "input[name='row[is_limit_level]']", function (e, ref) {
                    $(".islimitlevel").addClass("hidden");
                    $(".islimitlevel.islimitlevel-" + $(this).val()).removeClass("hidden");
                });

                // 限制商品分类
                $(document).on("click fa.event.typeupdated", "input[name='row[limit_goods_category]']", function (e, ref) {
                    $(".limitgoodscategory").addClass("hidden");
                    $(".limitgoodscategory.limitgoodscategory-" + $(this).val()).removeClass("hidden");
                });

                // 限制商品
                $(document).on("click fa.event.typeupdated", "input[name='row[limit_goods]']", function (e, ref) {
                    $(".limitgoods").addClass("hidden");
                    $(".limitgoods.limitgoods-" + $(this).val()).removeClass("hidden");
                });
                $(document).on('click', "input[name='row[is_index]']", function () {
                    var em = $('.total');
                    var icon = $('.icon');
                    var max = $('.gettype-1 input');
                    if ($(this).val() == 1) {
                        // em.hide();
                        // icon.show();
                        // max.val(1);
                        em.show();
                        icon.hide();
                        max.val(max.data('value'));
                    } else {
                        em.show();
                        icon.hide();
                        max.val(max.data('value'));
                    }
                });
            }
        }
    };
    return Controller;
});