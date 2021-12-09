define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'realname/authentication/index',
                    add_url: 'realname/authentication/add',
                    edit_url: 'realname/authentication/edit',
                    del_url: 'realname/authentication/del',
                    multi_url: 'realname/authentication/multi',
                    by_url: '/admin/realname/Authentication/by',
                    refuse_url: '/admin/realname/Authentication/refuse',
                    table: 'store',
                }
            });

            var table = $("#table");


            // var array = {};
            // var data = $.getJSON('admin/realname/Authentication/selectinfo');
            // data.success(function (data) {
            //     array = data;
            // })

            // Fast.api.ajax({
            //     url:'',
            //     data:'',
            // },function (data) {
            //     ''
            // },function (data) {
            //     'error'
            // })
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user.username', title: __('Uid'),operate:'LIKE'},
                        {field: 'store_name', title: __('Store_name'),operate:'LIKE'},
                        {field: 'store_manager', title: __('Store_manager'),operate:'LIKE'},
                        {field: 'mobile', title: __('Mobile')},
                        {
                            field: 'province_id',
                            title: '地区',
                            visible: false,
                            searchList: function (column) {
                                //在index.html 页面有#categorytpl
                                return Template('categorytpl', {});
                            }
                        },
                        {
                            field: 'store_address',
                            title: '店铺地址',
                            // searchList: data,
                            // formatter:function(value){
                            //     return array[value]
                            // },
                            operate: false,
                        },
                        {
                            field: 'address', title: __('Address'), width: '25%',
                            cellStyle: function (value, row, index, field) {
                                return {
                                    css: {
                                        "min-width": "150px",
                                        "white-space": "nowrap",
                                        "text-overflow": "ellipsis",
                                        "overflow": "hidden",
                                        "max-width": "300px"
                                    }
                                };
                            }
                        },
                        // {
                        //     field: 'idcard_front',
                        //     title: __('Idcard_front'),
                        //     formatter: Table.api.formatter.image,
                        //     operate: false
                        // },
                        // {
                        //     field: 'idcard_back',
                        //     title: __('Idcard_back'),
                        //     formatter: Table.api.formatter.image,
                        //     operate: false
                        // },
                        // {
                        //     field: 'store_front_photo',
                        //     title: __('Store_front_photo'),
                        //     formatter: Table.api.formatter.image,
                        //     operate: false
                        //
                        // },
                        // {
                        //     field: 'business_license',
                        //     title: __('Business_license'),
                        //     formatter: Table.api.formatter.image,
                        //     operate: false,
                        // },
                        // {field: 'store_status', title: __('Store_status')},
                        {
                            field: 'store_status',
                            title: __('申请状态'),
                            searchList: {"0": '待审核', "1": '已通过', "2": '已拒绝'},
                            formatter: Table.api.formatter.normal
                        },
                        /*{
                            field: 'audit_time',
                            title: __('Audit_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },*/
                        {
                            field: 'add_time',
                            title: __('Add_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        //{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        {
                            field: 'operate',
                            width: "120px",
                            title: '审核',
                            table: table,
                            events: Table.api.events.operate,
                            operate: false,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('立即审核'),
                                    classname: 'btn btn-xs btn-primary btn-dialog btn-yc',
                                    icon: 'fa fa-wrench',
                                    url: $.fn.bootstrapTable.defaults.extend.refuse_url,
                                },
                                {
                                    name: '详情',
                                    title: '详情',
                                    text: '详情',
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: $.fn.bootstrapTable.defaults.extend.edit_url,
                                },
                                {
                                    name: 'del',
                                    text: '删除',
                                    icon: 'fa fa-trash',
                                    title: __('Del'),
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    confirm: '确认删除?',
                                    url: $.fn.bootstrapTable.defaults.extend.del_url,
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger('click');
                                        Toastr.success('删除成功');
                                        return false;
                                    },
                                    error: function (data, ret) {
                                        Toastr.error('删除失败');
                                        return false;
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                    ]
                ]
            });

            //在表格内容渲染完成后回调的事件

            //给表格绑定点击查看事件

            table.on('post-body.bs.table', function (e, json) {

                $("tbody tr[data-index]", this).each(function (k, v) {

                    if (json[k]['store_status'] != 0) {
                        $("input[type=checkbox]", this).prop("disabled", true);
                        $("td:last", this).find('.btn-yc').addClass("hidden");
                    }
                });
            });
            //
            // // 获取选中项
            // $(document).on("click", ".btn-selected", function () {
            //     var that = $(this);
            //     layer.confirm('确认审核通过？', {
            //         btn: ['通过', '取消'] //按钮
            //     }, function () {
            //         var ids = Table.api.selectedids(table);
            //         ids = ids.join(',');
            //         $.getJSON('realname/Authentication/by', {'ids': ids}, function (data) {
            //             console.log(data.data);
            //             if (data.data > 0) {
            //                 layer.msg('通过成功！', {icon: 1}, 1800);
            //                 $(".btn-refresh").trigger('click');
            //             }
            //             esle
            //             {
            //                 layer.msg('操作失败！', {icon: 2}, 1800)
            //             }
            //         })
            //
            //     }, function () {
            //         layer.msg('操作失败！', {icon: 2}, 1800)
            //     });
            //选中的
            //table.bootstrapTable('getSelections')
            // if (table.bootstrapTable('getSelections').length != 0) {
            //     Layer.alert(JSON.stringify(table.bootstrapTable('getSelections')));
            // } else {
            //     //在templateView的模式下不能调用table.bootstrapTable('getSelections')来获取选中的ID,只能通过下面的Table.api.selectedids来获取
            //     Layer.alert(JSON.stringify(Table.api.selectedids(table)));
            // }

            // });
            // 为表格绑定事件
            Table.api.bindevent(table);

            // $(document).on("click", ".btn-by,.btn-refuse", function () {
            //             //     var ids = Table.api.selectedids(table);
            //             //     var url = $(this).data('url');
            //             //     Layer.confirm($(this).data('confirm'), {title: '提示'}, function (index) {
            //             //         Fast.api.ajax({
            //             //             url: url,
            //             //             data: {ids: ids.join(",")}
            //             //         }, function () {
            //             //             $(".btn-refresh").trigger("click");
            //             //         });
            //             //         Layer.close(index);
            //             //     });
            //             // });
            $(document).on('click', '.btn-by,.btn-refuse', function () {
                var that = this;
                //循环弹出多个编辑框
                $.each(table.bootstrapTable('getSelections'), function (index, row) {
                    Fast.api.open($(that).data('url') + '/ids/' + row.id, $(that).data('title'));
                });
            })
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        by: function () {
            Controller.api.bindevent();
        }, refuse: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
        }
    };
    return Controller;
});