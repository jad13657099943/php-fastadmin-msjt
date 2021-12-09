define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/curriculum/curriculum/index',
                    add_url: 'msjt/curriculum/curriculum/add',
                    edit_url: 'msjt/curriculum/curriculum/edit',
                    del_url: 'msjt/curriculum/curriculum/del',
                    multi_url: 'msjt/curriculum/curriculum/multi',
                    table: 'msjt_goods_curriculum',
                    dragsort_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                searchFormVisible:true,
                search:false,
                showToggle:false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate: false},
                        {field: 'name', title: __('Name')},
                        {field: 'type_id', title: __('Type_id'),searchList: $.getJSON('msjt/curriculum/type/typeLists')},
                        {field: 'simages', title: __('Simages'),operate:false, formatter: Table.api.formatter.images},
                   //     {field: 'fimages', title: __('Fimages'), formatter: Table.api.formatter.images},
                        {field: 'money', title: __('Money'), operate:false},
                        {field: 'freedate', title: __('Freedate'), searchList: {"1":__('Freedate 1'),"2":__('Freedate 2')}, formatter: Table.api.formatter.normal},
                        {field: 'statedata', title: __('Statedata'), searchList: {"1":__('Statedata 1'),"2":__('Statedata 2')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                     //   {field: 'weigh', title: __('Weigh')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                      //  {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'video',
                                    text: '视频列表',
                                    title: '视频列表',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-success btn-addtabs',
                                    url: 'msjt/curriculum/video/index',
                                    visible:function (row){
                                        if(row.statedata==1){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }
                                }
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