define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/channel/index',
                    add_url: 'cms/channel/add',
                    edit_url: 'cms/channel/edit',
                    del_url: 'cms/channel/del',
                    multi_url: 'cms/channel/multi',
                    dragsort_url: '',
                    table: 'channel',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                pagination: false,
                escape: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'type',
                            title: __('Type'),
                            custom: {channel: 'info', list: 'success', link: 'primary'},
                            formatter: Table.api.formatter.flag
                        },
                        {field: 'model_name', title: __('Model_name'), operate: false},
                        {field: 'name', title: __('Name'), align: 'left'},
                      /*  {
                            field: 'name',
                            sortable: true,
                            title: __('Name'),
                            formatter: function (value, row, index) {
                                return '<input type="text" class="form-control text-center text-name" data-id="' + row.id + '" value="' + value + '" style="width:50px;margin:0 auto;" />';
                            },
                            events: {
                                "dblclick .text-name": function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    return false;
                                }
                            }
                        },*/
                     /*   {
                            field: 'url', title: __('Url'), formatter: function (value, row, index) {
                                return '<a href="' + value + '" target="_blank" class="btn btn-default btn-xs"><i class="fa fa-link"></i></a>';
                            }
                        },*/
                        {field: 'items', sortable: true, title: __('Items')},
                        {
                            field: 'weigh',
                            sortable: true,
                            title: __('Weigh'),
                            formatter: function (value, row, index) {
                                return '<input type="text" class="form-control text-center text-weigh" data-id="' + row.id + '" value="' + value + '" style="width:50px;margin:0 auto;" />';
                            },
                            events: {
                                "dblclick .text-weigh": function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    return false;
                                }
                            }
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            visible: false,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'updatetime',
                            title: __('Updatetime'),
                            visible: false,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                       // {field: 'iscontribute', title: __('Iscontribute'), searchList: {"1": __('Yes'), "0": __('No')}, formatter: Table.api.formatter.toggle},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ],
                search: false,
                commonSearch: false
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

            $(document).on("change", ".text-weigh", function () {
                $(this).data("params", {weigh: $(this).val()});
                Table.api.multi('', [$(this).data("id")], table, this);
                return false;
            });
           /* $(document).on("change", ".text-name", function () {
                $(this).data("params", {name: $(this).val()});
                Table.api.multi('', [$(this).data("id")], table, this);
                return false;
            });*/

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
            $("input[name='row[type]']:first").trigger("click");
            $("select[name='row[model_id]']").trigger("change");
        },
        edit: function () {
            Controller.api.bindevent();
            $("input[name='row[type]']:checked").trigger("fa.event.typeupdated", "edit");
        },
        admin: function () {

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/channel/admin',
                    dragsort_url: '',
                    table: 'channel_admin',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                pagination: false,
                escape: false,
                columns: [
                    [
                        {
                            field: 'username', title: __('Username')
                        },
                        {
                            field: 'superadmin', title: __('Type'), formatter: function (value, row, index) {
                                return row.superadmin ? "<span class='label label-danger'>超级管理员</span>" : "<span class='label label-success'>普通管理员</span>";
                            }
                        },
                        {field: 'channels', title: __('Channels')},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            formatter: Table.api.formatter.buttons,
                            buttons: [
                                {
                                    name: 'authorization',
                                    text: __('Authorization'),
                                    classname: 'btn btn-xs btn-success btn-authorization',
                                    icon: 'fa fa-list',
                                    url: 'cms/channel/admin/act/authorization',
                                    visible: function (row) {
                                        return !row.superadmin;
                                    },
                                },
                                {
                                    name: 'remove',
                                    text: __('Remove'),
                                    classname: 'btn btn-xs btn-danger btn-remove btn-ajax',
                                    icon: 'fa fa-times',
                                    url: 'cms/channel/admin/act/remove',
                                    visible: function (row) {
                                        return row.channels > 0;
                                    },
                                    confirm: __('Are you sure you want to remove this item?'),
                                    success: function (ret) {
                                        $(".btn-refresh").trigger("click");
                                    }
                                }
                            ]
                        }
                    ]
                ],
                search: false,
                commonSearch: false
            });
            // 为表格绑定事件
            Table.api.bindevent(table);

            require(['jstree'], function () {
                //全选和展开
                $(document).on("click", "#checkall", function () {
                    $("#channeltree").jstree($(this).prop("checked") ? "check_all" : "uncheck_all");
                });
                $(document).on("click", "#expandall", function () {
                    $("#channeltree").jstree($(this).prop("checked") ? "open_all" : "close_all");
                });

                // 点击授权
                $(document).on("click", ".btn-authorization", function () {
                    var row = Table.api.getrowbyindex(table, $(this).data("row-index"));
                    Fast.api.ajax($(this).attr("href"), function (data, ret) {
                        Layer.open({
                            id: "auth",
                            type: 1,
                            title: __('Authorization'),
                            btn: [__('Save')],
                            area: ["600px", "400px"],
                            content: Template("authorizationtpl", {}),
                            success: function () {
                                $('#channeltree').jstree({
                                    "themes": {
                                        "stripes": true
                                    },
                                    "checkbox": {
                                        "keep_selected_style": false,
                                    },
                                    "types": {
                                        "channel": {
                                            "icon": "fa fa-th",
                                        },
                                        "list": {
                                            "icon": "fa fa-list",
                                        },
                                        "link": {
                                            "icon": "fa fa-link",
                                        },
                                        "disabled": {
                                            "check_node": false,
                                            "uncheck_node": false
                                        }
                                    },
                                    'plugins': ["types", "checkbox"],
                                    "core": {
                                        "multiple": true,
                                        'check_callback': true,
                                        "data": data
                                    }
                                });
                            },
                            yes: function (index, o) {
                                var selected = $("#channeltree", o).jstree("get_selected");
                                if (selected.length <= 0) {
                                    Layer.msg(__('You must choose at least one channel'), {id: "aaafd"});
                                } else {
                                    Fast.api.ajax({
                                        url: "cms/channel/admin/act/save/ids/" + row.id,
                                        data: {"ids": selected.join(",")}
                                    }, function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                        Layer.close(index);
                                    });
                                }
                            }
                        });
                        return false;
                    });
                    return false;
                });
            });
        },
        api: {
            bindevent: function () {
                $.validator.config({
                    rules: {
                        single: function (element) {
                            return !$("#c-name").val().match(/\n/);
                        },
                        channelname: function (element) {
                            if (element.value.toString().match(/^\d+$/)) {
                                return __('Can not be digital');
                            }
                            return $.ajax({
                                url: 'cms/channel/check_element_available',
                                type: 'POST',
                                data: {id: $("#c-name").val(), name: element.name, value: element.value},
                                dataType: 'json'
                            });
                        },
                        diyname: function (element) {
                            if (element.value.toString().match(/^\d+$/)) {
                                return __('Can not be digital');
                            }
                            return $.ajax({
                                url: 'cms/channel/check_element_available',
                                type: 'POST',
                                data: {id: $("#channel-id").val(), name: element.name, value: element.value},
                                dataType: 'json'
                            });
                        }
                    }
                });
                //不可见的元素不验证
                $("form[role=form]").data("validator-options", {ignore: ':hidden'});
                $(document).on("click fa.event.typeupdated", "input[name='row[type]']", function (e, ref) {
                    $(".tf").addClass("hidden");
                    $(".tf.tf-" + $(this).val()).removeClass("hidden");
                    if (typeof ref == 'undefined') {
                        $("select[name='row[model_id]']").trigger("change");
                    }
                    if ($(this).val() == 'link') {
                        $("#parent_id option").prop("disabled", false);
                    }
                });
                Form.api.bindevent($("form[role=form]"));
                $(document).on("change", "select[name='row[model_id]']", function () {
                    var parentChannel = $("#parent_id");
                    $("option[value=0]", parentChannel).prop("selected", true);
                    $("option[data-model]", parentChannel).prop("disabled", true);
                    $("option[data-model='" + $(this).val() + "']", parentChannel).prop("disabled", false);
                    var data = $("option:selected", this).data();
                    var type = $("input[name='row[type]']:checked").val();
                    if (type == 'channel') {
                        $("input[name='row[channeltpl]']").val(data.channeltpl).prev().val(data.channeltpl);
                    } else if (type == 'list') {
                        $("input[name='row[listtpl]']").val(data.listtpl).prev().val(data.listtpl);
                        $("input[name='row[showtpl]']").val(data.showtpl).prev().val(data.showtpl);
                    }
                });
                $(document).on("click", ".btn-select-page", function () {
                    var url = $(this).data("url");
                    parent.Fast.api.open(url, "选择单页", {
                        callback: function (data) {
                            $("#c-outlink").val(data.url);
                        }
                    });
                });

            }
        }
    };
    return Controller;
});