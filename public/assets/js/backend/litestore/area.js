define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'litestore/area/index',
                    add_url: 'litestore/area/add',
                    edit_url: 'litestore/area/edit',
                    del_url: 'litestore/area/del',
                    multi_url: 'litestore/area/multi',
                    table: 'area',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weight',
                sortOrder: 'desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'name', title: __('Name'), align: 'left'},
                        {
                            field: 'status',
                            title: __('status'),
                            operate: false,
                            searchList: {'0': '正常', '1': '禁用'},
                            custom: {'0': 'success', '1': 'danger'},
                            formatter: Table.api.formatter.status
                        },
                        {field: 'weigh', title: '权重', operate: false},
                        // {field: 'zip', title: __('Zip'),operate:false},
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
            Controller.api.setcity($('#type').val());
            Controller.api.setarea($('#type').val());
            $(document).on('change', '#type', function () {
                Controller.api.setcity($('#type').val());
            });
            $(document).on('change', '#types', function () {
                Controller.api.setarea($('#types').val());
            })
        },
        edit: function () {
            Controller.api.bindevent();
            Controller.api.modifycity($('#type').val());
            $('#type').attr('disable', 'disable').css("background-color", "#EEEEEE");
            Controller.api.modifyarea($('#types').val());
            $('#types').attr('disable', 'disable').css("background-color", "#EEEEEE");
            $(document).on('change', '#type', function () {
                Controller.api.modifycity($('#type').val());
            });
            $(document).on('change', '#types', function () {
                Controller.api.modifyarea($('#types').val());
            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            setcity: function (dis) {
                if (dis !== null && dis !== '') {
                    $('.params').text('市名');
                } else {
                    $('.params').text('省名');
                }
            },
            setarea: function (dis) {
                if (dis != null && dis !== '') {
                    $('.params').text('区/县名');
                }
            },
            modifycity: function (dis) {
                if (dis != null && dis !== '') {
                    $('.params').text('市名');
                } else {
                    $('.params').text('省名');
                }
            },
            modifyarea: function (dis) {
                if (dis != null) {
                    $('.params').text('区/县名');
                }
            }
        }
        // add: function () {
        //     Controller.api.bindevent();
        //     Controller.api.setClass($('#type').val());
        //     Controller.api.setDataRule($('#type').val());
        //     //Controller.api.getVersionList($('#type').val());
        //     $(document).on('change', '#type', function () {
        //         Controller.api.setClass($('#type').val());
        //         Controller.api.setDataRule($('#type').val());
        //         //Controller.api.getVersionList($('#type').val());
        //     });
        // },
        // edit: function () {
        //     Controller.api.bindevent();
        //     Controller.api.setClass($('#type').val());
        //     $(document).on('change', '#type', function () {
        //         Controller.api.setClass($('#type').val());
        //     });
        // },
        // api: {
        //     bindevent: function () {
        //         Form.api.bindevent($("form[role=form]"));
        //     },
        //     setClass: function (id) {
        //         if (id == 2) {
        //             $('.package').removeClass('hidden');
        //             $('.download').removeClass('hidden');
        //             $('.enforce').removeClass('hidden');
        //         } else {
        //             $('.package').addClass('hidden');
        //             $('.download').addClass('hidden');
        //             $('.enforce').addClass('hidden');
        //         }
        //     },
        //     setDataRule: function (id) {
        //         if (id == 2) {
        //             $('#c-packagesize').attr('data-rule', 'required');
        //             $('#c-packagesize').attr('data-rule', 'required');
        //         } else {
        //             $('#c-packagesize').removeAttr('data-rule');
        //             $('#c-packagesize').removeAttr('data-rule');
        //         }
        //     },
        //     getVersionList: function (id) {
        //         Fast.api.ajax({
        //             url: 'version/getVersionList',
        //             data: {id: id},
        //         }, function (data) {
        //             if (data != null) {
        //                 $('#c-oldversion').html('');
        //                 data.forEach(function (element, index) {
        //                     $('#c-oldversion').append("<option value=" + element + ">" + element + "</option>");
        //                 });
        //             } else {
        //                 $('#c-oldversion').append(null);
        //             }
        //             $('#c-oldversion').selectpicker('refresh');
        //             return false;
        //         });
        //     },
        // }
    };
    return Controller;
});