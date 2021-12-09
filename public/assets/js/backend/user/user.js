define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    // del_url: 'user/user/del',
                    moneyAdd_url: 'user/user/aa',
                    qrcodeAdd_url: 'user/user/aa',
                    multi_url: 'user/user/multi',
                    table: 'user',

                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                // showExport: true,
                // exportDataType: 'selected',
                // queryParams: function (params) {
                //     params.filter = JSON.stringify({'ids': Config.ids});
                //     params.op = JSON.stringify({'ids': '='});
                //     console.log(params);
                //     return params;
                // },
                columns: [
                    [
                        {checkbox: true}, // 是否展示全选功能 true 展示  false 隐藏
                        {field: 'id', title: __('Id'), sortable: true, operate: "="}, // id 跟数据库对应字段   __('Id') 配置文件字段  sortable 是否需要根据某个字段排序  true 需要  false 不需要
                        //  {field: 'group.name', title: __('Group')},
                        {field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'nickname', title: __('Username'), operate: 'LIKE'}, //operate: 'LIKE'  根据某个字段 like搜索
                        //{field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        // {field: 'email', title: __('Email'), operate: 'LIKE'},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},

                        //Table.api.formatter.image 头像控件   operate: false 是否需要搜索某个字段  默认全部都需要 false 不需要

                        // {field: 'level.name', title: __('Level'), operate: false, sortable: false}, //operate: 'BETWEEN', 根据某个字段区间搜索

                        {
                            field: 'vip_type',
                            title: __('Level'),
                            operate: "=",
                            searchList: {'0': '普通用户', '1': '普通VIP', '2': '尊享VIP', '3': '代理商'},
                            formatter: Table.api.formatter.normal
                        },

                        //operate: 'BETWEEN', 根据某个字段区间搜索
                        // {field: 'gender', title: __('Gender'), formatter: Table.api.formatter.status, searchList: {1: __('Male'), 2: __('Female')},operate: false},
                        //visible: false, 列表是否隐藏某个字段 false 隐藏 默认不隐藏  searchList: {1: __('Male'), 0: __('Female')} 某个字段下拉列表搜素
                            {field: 'money', title: __('Balance'), operate: false, sortable: true,formatter:Table.api.formatter.dialog,table:table,url:'user/user/moneylog'},
                        {
                            field: 'score',
                            title: __('Score'),
                            operate: false,
                            sortable: true,
                            formatter: Table.api.formatter.dialog,
                            table: table,
                            url: 'user/user/scorelog'
                        },
                        // {field: 'birthday', title: __('Birthday'), formatter: Table.api.formatter.date, addclass: 'datetimerange'},
                        {
                            field: 'distributor',
                            title: __('distributor'),
                            formatter: Table.api.formatter.status,
                            searchList: {1: __('distributor 1'), 2: __('distributor 2'), 0: __('distributor 0')},
                            operate: false
                        },
                        // {field: 'invite_num', title: __('invite_num'), operate: false, sortable: true},
                        //{field: 'successions', title: __('Successions'), visible: false, operate: 'BETWEEN', sortable: true},
                        //{field: 'maxsuccessions', title: __('Maxsuccessions'), visible: false, operate: 'BETWEEN', sortable: true},
                        //{field: 'logintime', title: __('Logintime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        //Table.api.formatter.datetime 时间控件  operate: 'RANGE', addclass: 'datetimerange', 时间控件搜索
                        //{field: 'loginip', title: __('Loginip')}, //搜索框控件
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {normal: __('Normal'), hidden: __('Hidden')},
                            operate: false
                        },
                        //{field: 'pid', title: __('pid'),operate: false},
                        {
                            field: 'jrkj_user.createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            visible: false,
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: false,
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },

                        // {field: 'joinip', title: __('Joinip'), formatter: false},

                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            buttons: [
                                {name: 'detail', text: '余额充值', title: '余额充值', icon: 'fa fa-list', classname: 'btn btn-xs btn-danger btn-dialog', url: 'user/User/moneyadd'},
                                {name: 'detail', text: '积分充值', title: '积分充值', icon: 'fa fa-list', classname: 'btn btn-xs btn-success btn-dialog', url: 'user/User/qrcodeadd'}
                            ],
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,

                        }
                    ]
                ]
            });

            $(document).on('click', '.btn-transfer', function () {
                Fast.api.open('distribution/user/transfer/type/2/ids/' + Config.ids, '转让用户', {
                    callback: function (data) {
                        var users = Table.api.selectedids(table).join(',');
                        Fast.api.ajax({
                            url: 'user/user/transferUser',
                            data: {'users': users, 'fromUser': Config.ids, 'toUser': data.id}
                        }, function () {
                            table.bootstrapTable('refresh');
                            parent.$("#table").bootstrapTable('refresh');
                        })
                    }
                });
            })


            //*************************** 自定义export开始
            var submitForm = function (ids, layero) {
                var options = table.bootstrapTable('getOptions');
                console.log(options);
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
                Layer.confirm("请选择导出的选项<form action='" + Fast.api.fixurl("user/user/export") + "' method='post' target='_blank'><input type='hidden' name='ids' value='' /><input type='hidden' name='filter' ><input type='hidden' name='op'><input type='hidden' name='search'><input type='hidden' name='columns'></form>", {
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
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },

        moneyadd: function () {
            Controller.api.bindevent();
        },
        qrcodeadd: function () {
            Controller.api.bindevent();
        },

        //佣金明细
        moneylog: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/moneylog',
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
                        // {field: 'id', title: __('ID')},
                        {field: 'money', title: __('变更金额'), operate: false},
                        {field: 'before', title: __('变更前余额'), operate: '='},

                        {field: 'after', title: __('变更后余额'), operate: 'LIKE'},

                        {field: 'memo', title: __('备注'), operate: 'LIKE'},

                        {
                            field: 'createtime',
                            title: __('创建时间'),
                            formatter: Table.api.formatter.datetime,
                            addclass: 'datetimerange',
                        },
                    ]
                ],

            });

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

        //积分明细
        scorelog: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/scorelog',
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
                        // {field: 'id', title: __('ID')},
                        {field: 'score', title: __('变更积分'), operate: false},
                        {field: 'before', title: __('变更前积分'), operate: '='},
                        {field: 'after', title: __('变更后积分'), operate: 'LIKE'},
                        {field: 'memo', title: __('备注'), operate: 'LIKE'},
                        {
                            field: 'createtime',
                            title: __('创建时间'),
                            formatter: Table.api.formatter.datetime,
                            addclass: 'datetimerange',
                        },
                    ]
                ],

            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'user/user/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'id', title: __('Id')},
                        {field: 'username', title: __('Username')},
                        {field: 'mobile', title: __('Mobile')},
                        {
                            field: 'delete_time',
                            title: __('删除时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('还原'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'user/user/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('销毁'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'user/user/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
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