define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'course/teacher/index' + location.search,
                    add_url: 'course/teacher/add',
                    edit_url: 'course/teacher/edit',
                    del_url: 'course/teacher/del',
                    multi_url: 'course/teacher/multi',
                    table: 'teacher',
                }
            });

            var table = $("#table");

            //给添加按钮添加data-area属性
            $(".btn-add").data("area", ["1000px", "800px"]);
            //当内容渲染完成给编辑按钮添加data-area属性
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                $(".btn-editone").data("area", ["1000px", "800px"]);
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'nickname', title: __('Nickname')},
                        {
                            field: 'avatar',
                            title: __('Avatar'),
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {
                            field: 'level_id', title: __('Level_id'),
                            searchList: {"10": '初级', "20": '中级', "30": '高级'},
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'gender', title: __('Gender'),
                            searchList: {"0": '男', "1": '女',},
                            formatter: Table.api.formatter.normal,
                            operate: false
                        },
                        {field: 'age', title: __('Age'), operate: false},
                        {
                            field: 'coursecateaory.name',
                            title: __('课程类型'),
                            visible: true,
                            operate: false,
                            searchList: function (column) {
                                return Template('coursecateaorytpl', {});
                            }
                        },
                        {
                            field: 'subjectcateaory.name',
                            title: __('学段年级'),
                            visible: true,
                            operate: false,
                            searchList: function (column) {
                                return Template('subjectcateaorytpl', {});
                            }
                        },

                        {field: 'phone', title: __('Phone'), operate: false},

                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {"10": '正常', "20": '异常',},
                            formatter: Table.api.formatter.status,
                            operate: false
                        },

                        {
                            field: 'createtime',
                            title: __('加入时间'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            extend: "autocomplete=off",
                            formatter: Table.api.formatter.datetime,
                            datetimeFormat: 'YYYY-MM-DD'
                        },
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
        },
        edit: function () {
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