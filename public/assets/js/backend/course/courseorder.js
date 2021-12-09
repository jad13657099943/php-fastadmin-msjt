define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'course/courseorder/index' + location.search,
                    add_url: 'course/courseorder/add',
                    // edit_url: 'course/courseorder/edit',
                    del_url: 'course/courseorder/del',
                    multi_url: 'course/courseorder/multi',
                    table: 'course_order',
                }
            });

            var course_id = $('#course-id').val();
            // alert(course_id);
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'order_on', title: __('Order_on')},
                        {field: 'uid', title: __('Uid'), operate: false, visible: false},
                        {field: 'username', title: __('Username'), operate: 'like'},
                        {field: 'transaction_id', title: __('Transaction_id'), visible: false, operate: false},
                        {
                            field: 'course.name', title: __('Course_id'),
                            operate: false,
                            formatter: Table.api.formatter.dialog,
                            table: table,
                            url: 'course/courselist/edit/course_id/{$row.course_id}'
                        },
                        {
                            field: 'coursecateaory.name',
                            title: __('Course_category_id'),
                            searchList: function () {
                                return Template('coursecateaorytpl', {});
                            },
                        },
                        {
                            field: 'subjectcateaory.name',
                            title: __('Subject_category_id'),
                            searchList: function () {
                                return Template('subjectcateaorytpl', {});
                            }
                        },

                        {field: 'teacher.nickname', title: __('Teacher_id'), operate: false},
                        {field: 'pay_price', title: __('Pay_price'), operate: false},
                        {field: 'remake', title: __('Remake'), visible: false, operate: false,},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            extend: "autocomplete=off",
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'updatetime',
                            title: __('Updatetime'),
                            addclass: 'datetimerange',
                            visible: false,
                            operate: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('Detail'),
                                    title: __('Detail'),
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    //btn btn-xs btn-success btn-editone
                                    url: 'course/courseorder/detail',
                                }
                            ],

                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ],
                queryParams: function (params) {
                    var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);
                    var course = $('#c-pid').val();
                    var subject = $('#b-pid').val();
                    if (course && course != 0) {
                        filter.course_category_id = course;
                        op.course_category_id = '=';
                    }
                    if (subject && subject != 0) {
                        filter.subject_category_id = subject;
                        op.subject_category_id = '=';
                    }

                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    return params;
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('post-body.bs.table', function (data) {
                $(".btn-dialog").data("area", ["1000px", "800px"]);
            });

        },
        add: function () {
            Controller.api.bindevent();
        },
        detail: function () {
            Controller.api.bindevent();
            //隐藏图片后的删除按钮(要放在绑定事件后面)
            $('#p-course li a:last').hide();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});