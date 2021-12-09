define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'msjt/curriculum/video/index/ids/'+Config.ids+location.search,
                    add_url: 'msjt/curriculum/video/add/ids/'+Config.ids,
                    edit_url: 'msjt/curriculum/video/edit',
                    del_url: 'msjt/curriculum/video/del',
                    multi_url: 'msjt/curriculum/video/multi',
                    table: 'msjt_goods_curriculum_video',
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
                      //  {field: 'curriculum_id', title: __('Curriculum_id')},
                        {field: 'title', title: __('Title')},
                      //  {field: 'urlfile', title: __('Urlfile')},
                      //  {field: 'freedate', title: __('Freedate'), searchList: {"1":__('Freedate 1'),"2":__('Freedate 2')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'weigh', title: __('Weigh'),operate: false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                       // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                       // {field: 'isVipdate', title: __('Isvipdate'), searchList: {"1":__('Isvipdate 1'),"2":__('Isvipdate 2')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {
                                    name: 'look',
                                    text: '查看视频',
                                    title: '查看视频',
                                    // extend:'data-area=\'["100%","100%"]\'',
                                    classname: 'btn btn-xs btn-success look',
                                }
                            ],
                            formatter: Table.api.formatter.operate}
                    ]
                ]
            });

           $('#return').click(function () {
               top.window.$("ul.nav-addtabs li.active").find(".fa-remove").trigger("click");
           });


            // 为表格绑定事件
            Table.api.bindevent(table);
            $(document).on('click', '.look', function (e) {
                var that = this;
                var options = $.extend({}, $(that).data() || {});
                var row = {};
                if (typeof options.tableId !== 'undefined') {
                    var index = parseInt(options.rowIndex);
                    var data = $("#" + options.tableId).bootstrapTable('getData');
                    row = typeof data[index] !== 'undefined' ? data[index] : {};
                }
                $.get('msjt/curriculum/video/look/ids/'+row.id,function (data){
                    window.open(data.data);
                });
            })
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