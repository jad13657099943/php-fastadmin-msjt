define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'litestore/litestorecategory/index',
                    add_url: 'litestore/litestorecategory/add',
                    edit_url: 'litestore/litestorecategory/edit',
                    del_url: 'litestore/litestorecategory/del',
                    multi_url: 'litestore/litestorecategory/multi',
                    table: 'litestore_category',
                }
            });

            var table = $("#table");

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
                            operate: 'false'
                        },
                        // {
                        //     field: 'recommendation',
                        //     title: 'Recommendation',
                        //     searchList: {'1': '是', '2': '否'},
                        //     formatter: Table.api.formatter.normal,
                        //     operate: 'false',
                        // },
                        // {
                        //     field: 'classification_image',
                        //     title: __('Poster'),
                        //     width: '120px',
                        //     formatter: image,
                        //     operate: 'false'
                        // },
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
                        // {field: 'status', title: __('Status'), formatter: Table.api.formatter.status, searchList: {normal: __('Normal'), hidden: __('Hidden')},operate:false},

                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        // {
                        //     field: 'updatetime',
                        //     title: __('Updatetime'),
                        //     operate: 'RANGE',
                        //     addclass: 'datetimerange',
                        //     formatter: Table.api.formatter.datetime
                        // },
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