define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'litestoregoods'], function ($, undefined, Backend, Table, Form, Template, litestoregoods) {

    var Controller = {
        index: function () {
            $(".btn-add").data("area", ["1000px", "800px"]);
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'litestore/litestoregoods/index',
                    add_url: 'litestore/litestoregoods/add',
                    edit_url: 'litestore/litestoregoods/edit',
                    del_url: 'litestore/litestoregoods/del',
                    multi_url: 'litestore/litestoregoods/multi',
                    table: 'litestore_goods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'goods_id',
                sortName: 'goods_sort',
                showExport: true,
                exportDataType: "all",
                exportTypes: ['excel'],
                queryParams: function (params) { //传递ajax参数  搜索框
                    var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);
                    if (Config.school_id) {
                        op['school_id'] = '=';
                        filter['school_id'] = Config.school_id;
                    }
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    return params;
                },
                // searchFormTemplate: 'customformtpl',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'goods_id', title: __('Goods_id'), operate: false},
                        {field: 'goods_name', title: __('Goods_name'), operate: false},

                        {
                            field: 'category_id', title: __('分类'), visible: false, searchList: function (column) {
                                return Template('categorytpl', {});
                            }
                        },
                        {
                            field: 'goods_status',
                            title: __('Goods_status'),
                            searchList: {"10": '出售中', "20": '已售完', "30": '仓库中', "40": '回收站',},
                            formatter: Table.api.formatter.normal,
                            visible: false
                        },
                        {field: 'image', title: __('Images'), formatter: Table.api.formatter.image, operate: false},
                        /*  {
                              field: 'spec_type',
                              title: __('Spec_type'),
                              searchList: {"10": __('Spec_type 10'), "20": __('Spec_type 20')},
                              formatter: Table.api.formatter.normal,
                              operate: false
                          },*/
                        {
                            field: 'deduct_stock_type',
                            visible: false,
                            title: __('Deduct_stock_type'),
                            searchList: {"10": __('Deduct_stock_type 10'), "20": __('Deduct_stock_type 20')},
                            formatter: Table.api.formatter.normal,
                            operate: false
                        },
                        {field: 'category.name', title: __('Category.name'), operate: false},
                        {field: 'goods_price', title: __('商城价'), operate: false},
                        /*   {field: 'freight.name', title: __('Freight.name')},*/
                        {field: 'vip_price', title: __('会员价格'), operate: false},
                        {field: 'sales_initial', title: __('Sales_initial'), operate: false},
                        {field: 'sales_actual', title: __('Sales_actual'), operate: false, sortable: true},
                        {field: 'goods_sort', title: __('Goods_sort'), operate: false},
                        /*         {field: 'delivery_id', visible title: __('Delivery_id')},*/
                        {
                            field: 'goods_status',
                            title: __('Goods_status'),
                            searchList: {"10": '出售中', "20": '已售完', "30": '仓库中', "40": '回收站',},
                            formatter: Table.api.formatter.normal,
                            operate: false
                        },
                        {
                            field: 'status',
                            title: '属性',
                            searchList: goodsStatus,
                            formatter: Table.api.formatter.normal,
                        },
                        {field: 'goods_name', title: __('Goods_name'), visible: false, operate: 'LIKE'},
                        // {field: 'is_delete', title: __('Is_delete'), searchList: {"0":__('Is_delete 0'),"1":__('Is_delete 1')}, formatter: Table.api.formatter.normal,operate:false},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        // {
                        //     field: 'uppershelf_time',
                        //     title: __('上架时间'),
                        //     formatter: Table.api.formatter.datetime,
                        //     operate: false
                        // },
                        /*  {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},*/
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
            // 切换基本信息/产品参数
            $('.tab').click(function (e) {
                let index = $(this).index()
                $('.tab').eq(index).addClass('active').siblings().removeClass('active')
                $('.tabletabs').hide()
                $('.tabletabs').eq(index).show()
            });

            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                Fast.api.close(data);
                Toastr.success("商品提交成功");
            }, function (data, ret) {
                Toastr.success("商品提交失败");
            }, function (success, error) {
                //  Toastr.success("111");
                //注意如果我们需要阻止表单，可以在此使用return false;即可
                //如果我们处理完成需要再次提交表单则可以使用submit提交,如下

                var form = this;
                if (form.size() === 0) {
                    Toastr.error("表单未初始化完成,无法提交");
                    return false;
                }
                var type = form.attr("method") ? form.attr("method").toUpperCase() : 'GET';
                type = type && (type === 'GET' || type === 'POST') ? type : 'GET';
                url = form.attr("action");
                url = url ? url : location.href;
                //修复当存在多选项元素时提交的BUG
                var params = {};
                var multipleList = $("[name$='[]']", form);
                if (multipleList.size() > 0) {
                    var postFields = form.serializeArray().map(function (obj) {
                        return $(obj).prop("name");
                    });
                    $.each(multipleList, function (i, j) {
                        if (postFields.indexOf($(this).prop("name")) < 0) {
                            params[$(this).prop("name")] = '';
                        }
                    });
                }

                var dataParam = {spec_many: specMany.getData()};
                console.log(dataParam);
                Fast.api.ajax({
                    type: type,
                    url: url,
                    data: form.serialize() + (Object.keys(params).length > 0 ? '&' + $.param(params) : '') + (Object.keys(dataParam).length > 0 ? '&' + $.param(dataParam) : ''),
                    dataType: 'json',
                    success: function (ret) {
                        Layer.close(Layer.load());
                        ret = Fast.events.onAjaxResponse(ret);
                        if (ret.code === 1) {
                            Fast.events.onAjaxSuccess(ret, success);
                        } else {
                            Layer.close(Layer.load());
                            Fast.events.onAjaxError(ret, error);

                        }
                    },
                    /*complete: function (xhr) {
                        var token = xhr.getResponseHeader('__token__');
                        if (token) {
                            $("input[name='__token__']", form).val(token);
                        }
                        //关闭弹窗
                        var index = parent.Layer.getFrameIndex(window.name);
                        var callback = parent.$("#layui-layer" + index).data("callback");
                        parent.Layer.close(index);
                        //刷新列表
                        parent.$("#table").bootstrapTable('refresh');
                    }*/
                }, function (data, ret) {
                    $('.form-group', form).removeClass('has-feedback has-success has-error');
                    if (data && typeof data === 'object') {
                        if (typeof data.token !== 'undefined') {
                            $("input[name='__token__']", form).val(data.token);
                        }
                        if (typeof data.callback !== 'undefined' && typeof data.callback === 'function') {
                            data.callback.call(form, data);
                        }
                    }
                }, function (data, ret) {
                    if (data && typeof data === 'object' && typeof data.token !== 'undefined') {
                        $("input[name='__token__']", form).val(data.token);
                    }
                });
                return false;
            });


            var $goodsSpecMany = $('.goods-spec-many')
                , $goodsSpecSingle = $('.goods-spec-single');

            $goodsSpecMany.hide() && $goodsSpecSingle.show();
            // 注册商品多规格组件
            var specMany = new GoodsSpec({
                container: '.goods-spec-many',
                OutForm: Form
            });

            // 切换单/多规格
            $('select[name="row[spec_type]"]').change(function (e) {
                var $goodsSpecMany = $('.goods-spec-many')
                    , $goodsSpecSingle = $('.goods-spec-single');

                if (e.currentTarget.value === '10') {
                    $goodsSpecMany.hide() && $goodsSpecSingle.show();
                } else {
                    $goodsSpecMany.show() && $goodsSpecSingle.hide();
                }
            });

        },
        edit: function () {

            $('.tab').click(function (e) {
                let index = $(this).index();
                $('.tab').eq(index).addClass('active').siblings().removeClass('active');
                $('.tabletabs').hide();
                $('.tabletabs').eq(index).show();
            });

            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                //Fast.api.close(data);
                Toastr.success("商品提交成功");
            }, function (data, ret) {
                Toastr.success("商品提交失败");
            }, function (success, error) {
                //注意如果我们需要阻止表单，可以在此使用return false;即可
                //如果我们处理完成需要再次提交表单则可以使用submit提交,如下
                //
                console.log(this);
                var form = this;
                if (form.size() === 0) {
                    Toastr.error("表单未初始化完成,无法提交");
                    return false;
                }

                var type = form.attr("method") ? form.attr("method").toUpperCase() : 'GET';
                type = type && (type === 'GET' || type === 'POST') ? type : 'GET';
                url = form.attr("action");
                url = url ? url : location.href;
                //修复当存在多选项元素时提交的BUG
                var params = {};
                var multipleList = $("[name$='[]']", form);
                if (multipleList.size() > 0) {
                    var postFields = form.serializeArray().map(function (obj) {
                        return $(obj).prop("name");
                    });
                    $.each(multipleList, function (i, j) {
                        if (postFields.indexOf($(this).prop("name")) < 0) {
                            params[$(this).prop("name")] = '';
                        }
                    });
                }
                var dataParam = {spec_many: specMany.getData()};
                console.log(dataParam);
                Fast.api.ajax({
                    type: type,
                    url: url,
                    data: form.serialize() + (Object.keys(params).length > 0 ? '&' + $.param(params) : '') + (Object.keys(dataParam).length > 0 ? '&' + $.param(dataParam) : ''),
                    dataType: 'json',
                    success: function (ret) {
                        Layer.close(Layer.load());
                        ret = Fast.events.onAjaxResponse(ret);
                        if (ret.code === 1) {
                            Fast.events.onAjaxSuccess(ret, success);
                        } else {
                            Layer.close(Layer.load());
                            Fast.events.onAjaxError(ret, error);

                        }
                    },

                    /*complete: function (xhr) {
                        var token = xhr.getResponseHeader('__token__');
                        if (token) {
                            $("input[name='__token__']", form).val(token);
                        }
                        //关闭弹窗
                        var index = parent.Layer.getFrameIndex(window.name);
                        var callback = parent.$("#layui-layer" + index).data("callback");
                        parent.Layer.close(index);
                        //刷新列表
                        parent.$("#table").bootstrapTable('refresh');
                    }*/
                }, function (data, ret) {
                    $('.form-group', form).removeClass('has-feedback has-success has-error');
                    if (data && typeof data === 'object') {
                        if (typeof data.token !== 'undefined') {
                            $("input[name='__token__']", form).val(data.token);
                        }
                        if (typeof data.callback !== 'undefined' && typeof data.callback === 'function') {
                            data.callback.call(form, data);
                        }
                    }
                }, function (data, ret) {
                    if (data && typeof data === 'object' && typeof data.token !== 'undefined') {
                        $("input[name='__token__']", form).val(data.token);
                    }
                });
                return false;
            });

            // 注册商品多规格组件
            var specMany = new GoodsSpec({
                container: '.goods-spec-many',
                OutForm: Form
            }, from_specData);

            // 切换单/多规格
            $('select[name="row[spec_type]"]').change(function (e) {
                var $goodsSpecMany = $('.goods-spec-many')
                    , $goodsSpecSingle = $('.goods-spec-single');
                if (e.currentTarget.value === '10') {
                    $goodsSpecMany.hide() && $goodsSpecSingle.show();
                } else {
                    $goodsSpecMany.show() && $goodsSpecSingle.hide();
                }
            });
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
            }
        }
    };
    return Controller;
});