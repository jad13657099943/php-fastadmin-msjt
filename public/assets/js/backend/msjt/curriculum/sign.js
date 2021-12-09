define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/curriculum/sign/index',
                    add_url: 'msjt/curriculum/sign/add',
                 //   edit_url: 'msjt/curriculum/sign/edit',
                    del_url: 'msjt/curriculum/sign/del',
                    multi_url: 'msjt/curriculum/sign/multi',
                    table: 'msjt_goods_curriculum_sign',
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
                       // {field: 'id', title: __('Id')},
                      //  {field: 'user_id', title: __('User_id')},
                       // {field: 'curriculum_id', title: __('Curriculum_id')},
                        {field: 'user.nickname',title:__('昵称')},
                        {field:'info.name',title:__('报名课程'),operate: false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                      //  {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'info',
                                    text: '查看详情',
                                    title: '查看详情',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-success btn-dialog info',
                                    url: 'msjt/curriculum/sign/info'
                                },
                            ],
                            formatter: Table.api.formatter.operate}
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