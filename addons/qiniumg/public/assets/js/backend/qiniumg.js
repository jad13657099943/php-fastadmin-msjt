define(['jquery', 'table', 'upload'], function ($, Table, Upload) {

    var marker = '';
    var prefix = '';

    var Controller = {
        index: function () {

            Table.api.init({
                extend: {
                    dragsort_url: '',
                    del_url: 'qiniumg/del'
                }
            });

            var table = $("#table");
            var tableOptions = {
                data: [],
                escape: false,
                pk: 'id',
                uniqueId: 'id',
                pagination: false,
                commonSearch: false,
                showRefresh: false,
                showColumns: false,
                showExport: false,
                trimOnSearch: true,
                searchOnEnterKey: true,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'key', title: __('key'), align: 'left',formatter:function(value,row, index){
                                var imghtml = '';
                                if(Config.qiniu_cdn !== '' && row.mimeType.indexOf('image') > -1){
                                    imghtml = ' <a href="javascript:;" data-url="' + Config.qiniu_cdn + row.key + '" class="qiniu-img text-success" title="查看图片" data-toggle="tooltip"><i class="fa fa-image"></i></a>';
                                }
                                if(table.data('operate-rename') === 1){
                                    return '<a href="javascript:;" class="qiniu-rename" data-id="'+row.id+'" data-toggle="tooltip" title="点击重命名">' +value + '</a> ' + imghtml;
                                }else{
                                    return value + imghtml;
                                }
                            }},
                        {field: 'mimeType', title: __('mimeType')},
                        {
                            field: 'type', title: __('type'),
                            formatter: function(value,row,index){
                                console.log(table.data('operate-changetype'));
                                if(table.data('operate-changetype') === 1){
                                    return Table.api.formatter.buttons.call(this,value,row,index);
                                }else{
                                    return row.type === 1 ? '低频存储' : '普通存储';
                                }
                            },
                            buttons: [
                                {
                                    name: 'changetype',
                                    title: '转为低频存储',
                                    text: '普通存储',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn-ajax',
                                    confirm: '确定转为低频存储吗？',
                                    url: 'qiniumg/changetype/ids/{ids}/type/1',
                                    hidden: function(row){
                                        return row.type === 1;
                                    },
                                    success:function(data){
                                        Controller.api.updateRow(table,data.key,{type: 1});
                                    }
                                },
                                {
                                    name: 'changetype',
                                    title: '转为普通存储',
                                    text: '低频存储',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn-ajax',
                                    confirm: '确定普通存储吗？',
                                    url: 'qiniumg/changetype/ids/{ids}/type/0',
                                    hidden: function(row){
                                        return row.type === 0;
                                    },
                                    success:function(data){
                                        Controller.api.updateRow(table,data.key,{type: 0});
                                    }
                                }
                            ],
                            table: table
                        },
                        {field: 'fsize', title: __('fsize')},
                        {field: 'putTime', title: __('putTime')},
                        {
                            field: 'status', title: __('status'), operate: false, formatter: Table.api.formatter.status,
                            custom: {0: 'success', 1: 'error'},
                            searchList: {0: '可用', 1: '禁用'}
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'download',
                                    icon: 'fa fa-download',
                                    title: '下载',
                                    extend: 'data-toggle="tooltip" target="_blank"',
                                    classname: 'btn btn-xs btn-success',
                                    url: function (row) {
                                        return Config.qiniu_cdn + row.key + '?attname=';
                                    }
                                },
                                {
                                    name: 'del',
                                    icon: 'fa fa-trash',
                                    title: __('Del'),
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    url: 'qiniumg/del/ids/{ids}',
                                    confirm: __('Are you sure you want to delete this item?'),
                                    success: function (data) {
                                        Controller.api.delRows(table, data.ids);
                                    }
                                }
                            ]
                        }
                    ]
                ]
            };
            // 初始化表格
            table.bootstrapTable(tableOptions);

            // 为表格绑定事件
            Table.api.bindevent(table);

            //表格加载完成
            table.on('post-body.bs.table',function(){
                console.log('load');
            });

            //上传时添加自定义参数
            Upload.events.onBeforeUpload = function(up, file){
                var param = {
                    path_prefix: $('#path-prefix').val(),
                    namerule   : $('#namerule').val()
                };
                Upload.list['plupload-qiniu'].setOption("multipart_params",param)
            };

            //上传按钮
            Upload.api.plupload('.plupload', function(up, ret){
                console.log(ret);
                if(ret.code === 1){
                    Toastr.success(ret.msg);
                    table.bootstrapTable('prepend',ret.data);
                }else{
                    Toastr.error(ret.msg);
                }
            }, function(){
                Toastr.error('上传出错啦');
            });

            $(document).on('click','.qiniu-img',function(){
                var json = {
                    "title": $(this).attr('title'),
                    "data": [
                        {src: $(this).data('url')}
                    ]
                };
                Layer.photos({photos: json});
            });

            //加载更多
            $(document).on('click', '#qiniu-load-more', function () {
                Controller.api.loadMore(table);
            });

            //重命名
            $(document).on('click', '.qiniu-rename', function () {
                var origin = $(this).text();
                var that = this;
                Layer.prompt({
                    formType: 2,
                    value: $(that).text(),
                    title: '重命名',
                    area: ['500px', '40px']
                },function(val, index){
                    if(origin === val){
                        Layer.msg('未改变值');
                    }else{
                        var id = $(that).data('id');
                        Fast.api.ajax({
                            url: 'qiniumg/rename',
                            data: {
                                id: id,
                                key: val
                            },
                            type: 'post'
                        },function(data){
                            Controller.api.updateRow(table,id,{key: val, id: data.id})
                        });
                    }
                    Layer.close(index);
                });
            });

            //批量删除按钮
            $('.qiniu-del').on('click',function(){
                var ids = Controller.api.selectedids(table);
                if(ids.length > 0){
                    Layer.confirm(
                        __('Are you sure you want to delete the %s selected item?', ids.length),
                        {icon: 3, title: __('Warning'),  shadeClose: true},
                        function (index) {
                            Fast.api.ajax({
                                url: 'qiniumg/del',
                                type: 'post',
                                data:{
                                    ids: ids
                                }
                            }, function(data){
                                Controller.api.delRows(table,data.ids);
                                Controller.api.updateDelBtn(table);
                            });
                            Layer.close(index);
                        }
                    );

                }
            });
            //checkbox选择事件
            $(document).on('change',"input[data-index]",function(){
                Controller.api.updateDelBtn(table);
            });
            //全选事件
            $(document).on('change','input[name="btSelectAll"]',function(){
                Controller.api.updateDelBtn(table);
            });

            //搜索
            table.on('search.bs.table', function (e, text) {
                prefix = text;
                marker = '';
                table.bootstrapTable('load', []);
                table.bootstrapTable('removeAll');

                Controller.api.loadMore(table);
            });

            $('#qiniu-load-more').trigger('click');

        },
        api: {
            loadMore: function (table) {
                $.ajax({
                    url: 'qiniumg/index',
                    type: 'POST',
                    data: {
                        marker: marker,
                        prefix: prefix
                    },
                    success: function (ret) {
                        if (ret.code === 1) {
                            if (typeof ret.data.marker === 'undefined') { //无更多数据
                                marker = '';
                                $('#qiniu-load-more').hide();
                            } else {
                                marker = ret.data.marker;
                                $('#qiniu-load-more').show();
                            }
                            if (ret.data.items.length <= 0) {
                                Toastr.error('无更多数据');
                            } else {
                                table.bootstrapTable('append', ret.data.items);
                            }

                        } else {
                            Toastr.error(ret.msg);
                        }
                    }
                });
            },
            delRows(table, ids){
                for (var i = 0; i < ids.length; i++) {
                    $('tr[data-uniqueid="'+ids[i]+'"]').remove();
                }
            },
            selectedids: function(table){
                var ids = $.map($("input[data-index]:checked",table), function (dom) {
                    return $(dom).parents('tr').data("uniqueid");
                });
                return ids;
            },
            updateDelBtn: function(table){
                var ids = Controller.api.selectedids(table);
                if(ids.length > 0){
                    $('.qiniu-del').removeClass('disabled');
                }else{
                    $('.qiniu-del').addClass('disabled');
                }
            },
            updateRow: function(table,key,data){
                var index = $('tr[data-uniqueid="'+key+'"]').data('index');
                var row = Table.api.getrowbyid(table,key);
                for(k in data){
                    row[k] = data[k];
                }
                table.bootstrapTable('updateRow',{index: index, row: row});
            }
        }
    };
    return Controller;
});