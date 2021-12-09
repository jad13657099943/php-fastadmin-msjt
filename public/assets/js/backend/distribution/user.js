define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'distribution/user/index',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");
            var level = {};
            var data = $.getJSON('distribution/user/getLevel', '', function (data) {
                level = data;
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showSearch: false,
                columns: [
                    [
                        {checkbox: true}, // 是否展示全选功能 true 展示  false 隐藏
                        // {field: 'id', title: __('Id'), sortable: true}, // id 跟数据库对应字段   __('Id') 配置文件字段  sortable 是否需要根据某个字段排序  true 需要  false 不需要
                        //  {field: 'group.name', title: __('Group')},
                        {field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'nickname', title: __('Nickname'), operate: false}, //operate: 'LIKE'  根据某个字段 like搜索
                        // {field: 'username', title: '姓名', operate: 'LIKE'}, //operate: 'LIKE'  根据某个字段 like搜索
                        //{field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        // {field: 'email', title: __('Email'), operate: 'LIKE'},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        // {field: 'apply_money', title: '代理商费用', operate: false},
                        // {field: 'agent.address', title: '省市区', visible: false, operate: 'like'},
                        // {field: 'agent.site', title: '详细地址', visible: false, operate: 'like'},

                        //Table.api.formatter.image 头像控件   operate: false 是否需要搜索某个字段  默认全部都需要 false 不需要

                        // {
                        //     field: 'vip_status', title: __('Level'), operate: false,
                        //     formatter: function (value) {
                        //         return value === 1 ? 'VIP' : '普通';
                        //     }
                        // }, //operate: 'BETWEEN', 根据某个字段区间搜索
                        /*{
                            field: 'gender',
                            title: __('Gender'),
                            formatter: Table.api.formatter.status,
                            searchList: {1: __('Male'), 2: __('Female')},
                            operate: false
                        },*/
                        //visible: false, 列表是否隐藏某个字段 false 隐藏 默认不隐藏  searchList: {1: __('Male'), 0: __('Female')} 某个字段下拉列表搜素

                        // {field: 'score', title: __('Score'), operate: false, sortable: true},
                        // {field: 'balance', title: __('Money'), operate: false, sortable: true},
                        /*{
                            field: 'distributor_id',
                            title: __('代理商'),
                            formatter: function (value) {
                                return level[value];
                            },
                            searchList: data,
                            // operate: false
                        },*/
                        {field: 'invite_num', title: __('invite_num'), operate: false, sortable: true},
                        // {field: 'count', title: __('采购VIP人数'), operate: false, sortable: true},
                        {field: 'total_balance', title: '总佣金', operate: false},
                        {field: 'balance', title: '可提现', operate: false},
                        {field: 'total_withdraw', title: '已提现', operate: false},
                        // {/*field: '',*/ title: '预计收益', operate: false},
                        //{field: 'successions', title: __('Successions'), visible: false, operate: 'BETWEEN', sortable: true},
                        //  {field: 'maxsuccessions', title: __('Maxsuccessions'), visible: false, operate: 'BETWEEN', sortable: true},
                        //   {field: 'logintime', title: __('Logintime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        //Table.api.formatter.datetime 时间控件  operate: 'RANGE', addclass: 'datetimerange', 时间控件搜索

                        // {field: 'loginip', title: __('Loginip')}, //搜索框控件
                        {
                            field: 'jointime',
                            title: __('Jointime'),
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true
                        },
                        // {field: 'joinip', title: __('Joinip'), formatter: false},
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {normal: __('Normal'), hidden: __('Hidden')},
                            operate: false
                        },
                        {
                            field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                // {
                                //     name: 'detail',
                                //     text: '详情',
                                //     classname: 'btn btn-xs btn-success btn-dialog',
                                //     icon: 'fa fa-clone',
                                //     url: 'distribution/user/detail',
                                // },
                                {
                                    name: 'commission',
                                    text: __('佣金明细'),
                                    icon: 'fa fa-wrench',
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    url: 'distribution/user/moneylog',
                                },
                                {
                                    name: 'team',
                                    text: __('我的团队'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-pencil',
                                    url: 'distribution/user/team',
                                },

                                {
                                    name: 'order',
                                    text: __('分销订单'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-clone',
                                    url: 'distribution/user/order',
                                },


                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]

            });


            //*************************** 自定义export开始

            var submitForm = function (ids, layero) {
                var options = table.bootstrapTable('getOptions');
            
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
                Layer.confirm("请选择导出的选项<form action='" + Fast.api.fixurl("distribution/user/export") + "' method='post' target='_blank'><input type='hidden' name='ids' value='' /><input type='hidden' name='filter' ><input type='hidden' name='op'><input type='hidden' name='search'><input type='hidden' name='columns'></form>", {
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
        },


        //佣金明细
        moneylog: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'distribution/user/moneylog',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                commonSearch: false,
                queryParams: function (params) { //传递ajax参数
                    params.filter = JSON.stringify({'ids': Config.ids});
                    params.op = JSON.stringify({'ids': '='});
                    return params;
                },
                columns: [
                    [
                        {field: 'id', title: __('ID')},
                        {field: 'price', title: __('Money'), operate: false},
                        {
                            field: 'add_time',
                            title: __('Createtime'),
                            formatter: Table.api.formatter.datetime,
                            addclass: 'datetimerange',
                        },
                    ]
                ],

            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        //分销订单
        order: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'distribution/user/order',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                commonSearch: false,
                queryParams: function (params) { //传递ajax参数
                    params.filter = JSON.stringify({'ids': Config.ids});
                    params.op = JSON.stringify({'ids': '='});
                    return params;
                },
                columns: [
                    [
                        {field: 'id', title: __('ID')},
                        {field: 'order_sn', title: __('Ordersn'), operate: false},
                        // {field: 'title', title: __('Title'), operate: false},

                        // {field: 'image', title: __('Image'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'money', title: __('Money'), operate: false},
                        {field: 'act_pay_money', title: __('Act_pay_money'), operate: false},
                        {field: 'nickname', title: __('会员昵称'), operate: false},
                        {
                            field: 'create_time',
                            title: __('Createtime'),
                            formatter: Table.api.formatter.datetime,
                            addclass: 'datetimerange',
                        },
                    ]
                ],

            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },


        //我的团队 -一级分销员
        team: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'distribution/user/team/ids/' + Config.ids,
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('ID')},
                        {field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'mobile', title: __('mobile'), operate: 'like'},
                        {
                            field: 'vip_type',
                            title: __('Level'),
                            operate: "=",
                            searchList: {'0': '普通用户', '1': '普通VIP', '2': '尊享VIP', '3': '代理商'},
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'username', title: __('username'), operate: 'LIKE'},
                        {field: 'invite_num', title: '推广人数', operate: false},
                        {
                            field: 'operate', title: __('Operate'),
                            table: table,
                            buttons: [
                                {
                                    name: 'team',
                                    text: 'ta的团队',
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-pencil',
                                    url: 'distribution/user/secondteam',
                                    visible: function (row) {
                                        return row.invite_num > 0 ? true : false;
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons,
                        },
                    ]
                ],
            });

            $(document).on('click', '.btn-transfer', function () {
                Fast.api.open('distribution/user/transfer/ids/' + Config.ids, '转让用户', {
                    callback: function (data) {
                        var users = Table.api.selectedids(table).join(',');
                        Fast.api.ajax({
                            url: 'distribution/user/transferUser',
                            data: {'users': users, 'fromUser': Config.ids, 'toUser': data.id}
                        }, function () {
                            table.bootstrapTable('refresh');
                            parent.$("#table").bootstrapTable('refresh');
                        })
                    }
                });
            })
            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        transfer: function () {
            // 初始化表格参数配置
            Table.api.init({});

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: 'distribution/user/transfer/ids/' + Config.ids,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'nickname', title: __('Username'), operate: false}, //operate: 'LIKE'  根据某个字段 like搜索
                        {field: 'username', title: '姓名', operate: 'LIKE'}, //operate: 'LIKE'  根据某个字段 like搜索
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {
                            field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'confirm',
                                    text: '转让',
                                    classname: 'btn btn-xs btn-success btn-click',
                                    confirm: '确认转让给该代理商吗?',
                                    icon: 'fa fa-clone',
                                    click: function (value, row) {
                                        Fast.api.close(row);
                                    },
                                },
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        detail: function () {
            Controller.api.bindevent();
        },
        // 初始化表格参数配置  我的团队 -二级分销员
        secondteam: function () {

            Table.api.init({
                extend: {
                    index_url: 'distribution/user/secondteam/ids/' + Config.ids,
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                commonSearch: false,
                queryParams: function (params) { //传递ajax参数
                    params.filter = JSON.stringify({'ids': Config.ids});
                    params.op = JSON.stringify({'ids': '='});
                    return params;
                },
                columns: [
                    [
                        {field: 'id', title: __('ID')},
                        {field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'mobile', title: __('mobile'), operate: '='},
                        {field: 'username', title: __('username'), operate: 'LIKE'},
                    ]
                ],

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

        select: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'distribution/user/select',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pagination: false,
                commonSearch: true,
                showToggle: false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('ID')},
                        {field: 'mobile', title: __('mobile'), operate: '='},
                        {field: 'username', title: __('username'), operate: 'LIKE'},
                        {
                            field: 'operate', title: __('Operate'), events: {
                                'click .btn-chooseone': function (e, value, row, index) {
                                    var multiple = Backend.api.query('multiple');
                                    multiple = multiple == 'true' ? true : false;
                                    row.url = row.id;
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
                    urlArr.push(j.id);
                });
                // console.log(urlArr.length);
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