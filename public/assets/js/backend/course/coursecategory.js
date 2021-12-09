define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'course/coursecategory/index',
                    add_url: 'course/coursecategory/add',
                    edit_url: 'course/coursecategory/edit',
                    del_url: 'course/coursecategory/del',
                    multi_url: 'course/coursecategory/multi',
                    table: 'course_category',
                }
            });

            var table = $("#table");

            //给添加按钮添加data-area属性
            $(".btn-add").data("area", ["1000px", "800px"]);
            //当内容渲染完成给编辑按钮添加data-area属性
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                $(".btn-editone").data("area", ["1000px", "800px"]);
            });

            function image(value) {
                value = value ? value : '/uploads/20191030/FqTJTySZa4IvLyDTZJFrIAlm55lv.gif';
                return '<a href="' + Fast.api.cdnurl(value) + '" target="_blank"><img class="img" style="height: 40px;" src="' + Fast.api.cdnurl(value) + '" /></a>';
            }

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                escape: false,
                sortName: 'weigh',
                sortOrder: 'dasc',
                pagination: false,   /*是否需要分页*/
                commonSearch: false, /*是否需要搜索*/
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        /*   {field: 'pid', title: __('Pid')},*/
                        //{field: 'name', title: __('Name')},
                        {field: 'name', title: __('Name'), align: 'left'},
                        {
                            field: 'image',
                            title: __('Classification'),
                            width: '120px',
                            formatter: image,
                            operate: 'false',
                            visible:false
                        },
                        {
                            field: 'classification_image',
                            title: __('Poster'),
                            width: '120px',
                            formatter: image,
                            operate: 'false',
                            visible:false
                        },
                        {field: 'weigh', title: __('Weigh')},
                        {
                            field: 'recommendation',
                            title: __('Recommendation'),
                            searchList: {'1': '是', '2': '否'},
                            formatter: Table.api.formatter.normal,
                            operate: 'false',
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {'10': __('Normal'), '20': __('Hidden')},
                            formatter: Table.api.formatter.normal,
                            operate: 'false',
                        },

                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
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
            table.on('load-success.bs.table', function (data) {
                $(".btn-editone").data("area", ["1000px", "800px"]);
            });
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