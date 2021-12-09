define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {
        index: function () {
            $(".btn-add").data("area", ["1000px", "800px"]);
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'course/courselist/index',
                    add_url: 'course/courselist/add',
                    edit_url: 'course/courselist/edit',
                    del_url: 'course/courselist/del',
                    multi_url: 'course/courselist/multi',
                    table: 'course',
                }
            });

            var table = $("#table");


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'sort',
                // searchFormTemplate: 'customformtpl',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('ID'), operate: false},
                        {field: 'name', title: __('Course_name'), operate: 'like'},
                        {field: 'image', title: __('Image'), formatter: Table.api.formatter.images, operate: false},
                        {
                            field: 'coursecateaory.name',
                            title: __('Course_Category'),
                            searchList: function (column) {
                                return Template('coursecateaorytpl', {});
                            }
                        },
                        {
                            field: 'subjectcateaory.name',
                            title: __('Subject_Category'),
                            searchList: function (column) {
                                return Template('subjectcateaorytpl', {});
                            }
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {"10": __('Normal'), "20": __('Aberrant'),},
                            formatter: Table.api.formatter.normal,
                            visible: false,
                            operate: false
                        },
                        {field: 'price', title: __('Price'), operate: false},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            extend: "autocomplete=off",
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            buttons: [
                                {
                                    name: 'discuss',
                                    text: __('Discuss'),
                                    title: __('Discuss'),
                                    icon: 'fa fa-comment-o',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'course/courselist/discuss',
                                }
                            ],
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        },
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
            table.on('load-success.bs.table', function (data) {
                $(".btn-editone").data("area", ["1000px", "800px"]);
            });
            table.on('post-body.bs.table', function (data) {
                $(".btn-dialog").data("area", ["1000px", "800px"]);
            });
        },
        add: function () {
            // 切换基本信息/产品参数
            $('.tab').click(function (e) {
                let index = $(this).index();
                $('.tab').eq(index).addClass('active').siblings().removeClass('active');
                $('.tabletabs').hide();
                $('.tabletabs').eq(index).show();
            });

            //直播 录播切换
            var $video_type = 0;

            $("input[name='row[video_type]']").change(function (e) {
                $video_type = e.currentTarget.value;
            });

            //上课时间 与 课程时间
            $(document).on('click', '.video-Append', function () {
                if ($video_type == '0') {
                    $('.video-a:last').empty();
                } else {
                    $('.video-b:last').empty();
                }
            });

            //绑定事件
            Controller.api.course();
            Controller.api.bindevent();
        },
        edit: function () {
            // 切换基本信息/产品参数
            $('.tab').click(function (e) {
                let index = $(this).index()
                $('.tab').eq(index).addClass('active').siblings().removeClass('active')
                $('.tabletabs').hide()
                $('.tabletabs').eq(index).show()
            });

            //直播录播切换
            var $video_type = $("#hied_video_type").val();

            $("input[name='row[video_type]']").change(function (e) {
                $video_type = e.currentTarget.value;
            });

            $(document).on('click', '.video-Append', function () {
                if ($video_type == '0') {
                    $('.video-a:last').empty();
                } else {
                    $('.video-b:last').empty();

                }
            });

            //绑定事件
            Controller.api.course();
            Controller.api.bindevent();
        },
        discuss: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'course/courselist/discuss/id/' + id,
                    del_url: 'course/courselist/del',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                commonSearch: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('ID'), operate: false, visible: false},
                        {field: 'user_name', title: __('Username'), operate: false},
                        {field: 'user_head', title: __('Head'), formatter: Table.api.formatter.images, operate: false},
                        {field: 'course.name', title: __('Course'), operate: false},
                        {
                            field: 'star', title: __('Star'),
                            searchList: {"1": __('GoodComment'), "2": __('MediumComment'), "3": __('NegativeComment')},
                            formatter: Table.api.formatter.normal,
                            operate: false
                        },
                        {
                            field: 'content',
                            title: __('Content'),
                            operate: false,
                            cellStyle: function (value, row, index, field) {
                                return {
                                    css: {
                                        "min-width": "150px",
                                        "white-space": "nowrap",
                                        "text-overflow": "ellipsis",
                                        "overflow": "hidden",
                                        "max-width": "280px"
                                    }
                                };
                            }
                        },
                        {
                            field: 'createtime',
                            title: __('DiscussTime'),
                            formatter: Table.api.formatter.datetime,
                            addclass: 'datetimerange',
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
                                    url: 'course/courselist/discussdetail',
                                }
                            ],
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        },
                    ]
                ],

            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            //隐藏图片后的删除按钮(要放在绑定事件后面)
            // $('#p-course li a:last').hide();
        },
        discussdetail: function () {
            Controller.api.course();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            course: function () {
                $(document).on("click", ".btn-append", function () {
                    //上传事件
                    Form.events.plupload($(".fieldlist .form-inline:last"));
                    //选择事件
                    Form.events.faselect($(".fieldlist .form-inline:last"));
                    //时间事件
                    Form.events.datetimepicker($(".fieldlist .form-inline:last"));

                    $(document).on('click', ".span-p,.span-v", function () {
                        var val = $('#' + $(this).data('id')).val();
                        window.open(val);
                        // Fast.common.openInNewWindow(val,'_blank',400,200);
                    });
                });
            }

        }
    };
    return Controller;
});