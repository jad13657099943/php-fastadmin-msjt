define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/block/index',
                    add_url: 'cms/block/add',
                    edit_url: 'cms/block/edit',
                    del_url: 'cms/block/del',
                    multi_url: 'cms/block/multi',
                    table: 'block',
                }
            });

            var table = $("#table");


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'block.id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', sortable: true, title: __('Id'), operate: false},
                        // {field: 'type', title: __('Type'), formatter: Table.api.formatter.search, searchList: Config.typeList},
                        {field: 'group.name', title: __('Group'), operate: false},
                        {
                            field: 'cate_id', title: __('分类搜索'), visible: false, searchList: function (column) {
                                return Template('categorytpl', {});
                            }
                        },
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        /*      {field: 'title', title: __('Title')},*/
                        {field: 'image', title: __('Image'), operate: false, formatter: Table.api.formatter.image},
                        //{field: 'url', title: '跳转ID', operate:false},
                        {field: 'weigh', title: __('weigh'), operate: false},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'updatetime',
                            title: __('Updatetime'),
                            sortable: true,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'jump_status',
                            title: __('Jump_status'),
                            operate: false,

                            // searchList: {"0": '商品详情', "1": '秒杀详情', "2": '拼团详情', "3": 'VIP专区',"4": '拼团列表',"5": '秒杀列表',
                            // "6" :'优惠券列表',"7": '申请代理',8: '邀请好友',"9" : 'WEB网页',},
                            searchList: {
                                '-1':__('无'),
                                '0': __('商品详情'),
                                '1': __('秒杀详情'),
                                '2': __('拼团详情'),
                                '3': __('VIP专区'),
                                '4': __('拼团列表'),
                                '5': __('秒杀列表'),
                                '6': __('优惠券列表'),
                                '7': __('申请代理'),
                                '8': __('邀请好友'),
                                '9': __('WEB网页'),
                                '10': __('分类'),
                            },
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            formatter: Table.api.formatter.status,
                            searchList: {normal: __('Normal'), hidden: __('Hidden')}
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
            Controller.api.block();

            /*根据现在的分类 显示隐藏*/
            $('select[name="row[cate_id]"]').change(function (e) {
                if (e.currentTarget.value === '21' || e.currentTarget.value === '24' || e.currentTarget.value === '30') {
                    $('.images').show();
                } else {
                    $('.images').hide();
                }

                if (e.currentTarget.value === '19' || e.currentTarget.value === '20' || e.currentTarget.value === '21' ||
                    e.currentTarget.value === '24') {
                    $('.url').show();
                } else {
                    $('.url').hide();
                }

                if (e.currentTarget.value !== '19') {
                    $('.jump').hide();
                } else {
                    $('.jump').show();
                }

                if (e.currentTarget.value === '21') {
                    $('._text').html('活动ID');
                    $('._text2').val('多个ID英文逗号隔开');
                }
            });

            /*根据选择跳转区域 限时隐藏*/
            $('select[name="row[jump_status]"]').change(function (e) {
                console.log(e.currentTarget.value);
                if (e.currentTarget.value === '1' || e.currentTarget.value === '2' || e.currentTarget.value === '0' || e.currentTarget.value === '9' || e.currentTarget.value === '10') {
                    $('.url').show();
                    $('._text').html('商品ID');
                    $('._text2').val('请填写商品ID’)');
                    if (e.currentTarget.value === '2' ||e.currentTarget.value === '1'){
                        $('._text').html('活动id');
                        $('._text2').val('请填写该营销活动id’)');
                    }
                    if (e.currentTarget.value === '9') {
                        $('._text').html('填写Url');
                        $('._text2').val('请填写Url(https开头)');
                    }
                    if (e.currentTarget.value === '10') {
                        $('._text').html('填写分类id');
                        $('._text2').val('注意是分类一级id');
                    }
                } else {
                    $('.url').hide();
                }

            });
        },

        edit: function () {
            Controller.api.bindevent();
            Controller.api.block();

            /*根据现在的分类 显示隐藏*/
            $('select[name="row[cate_id]"]').change(function (e) {
                console.log(e.currentTarget.value)
                if (e.currentTarget.value === '21' || e.currentTarget.value === '24' || e.currentTarget.value === '30') {
                    $('.images').show();
                } else {
                    $('.images').hide();
                }

                if (e.currentTarget.value === '19'  || e.currentTarget.value === '21' ||
                    e.currentTarget.value === '24') {
                    $('.url').show();
                } else {
                    $('.url').hide();
                }

                if (e.currentTarget.value !== '19') {
                    $('.jump').hide();
                } else {
                    $('.jump').show();
                }

                if (e.currentTarget.value === '21') {
                    $('._text').html('活动ID');
                    $('._text2').val('多个ID英文逗号隔开')
                }

            });

            /*根据选择跳转区域 限时隐藏*/
            $('select[name="row[jump_status]"]').change(function (e) {
                console.log(e.currentTarget.value);
                if (e.currentTarget.value === '0' || e.currentTarget.value === '1' || e.currentTarget.value === '2' || e.currentTarget.value === '9' || e.currentTarget.value === '10') {
                    $('.url').show();
                    $('._text').html('商品ID');
                    if (e.currentTarget.value === '2' ||e.currentTarget.value === '1'){
                        $('._text').html('活动id');
                    }
                    if (e.currentTarget.value === '9'){
                        $('._text').html('填写Url');
                    }
                    if (e.currentTarget.value === '10') {
                        $('._text').html('填写分类id');
                    }
                } else {
                    $('.url').hide();
                }

            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            block: function () {
                var id = $('#cate-id').val();
                var url = $('#jump_status').val();
                console.log(url);

                if (id === '21' || id === '24' || id === '30') {
                    $('.images').show();
                } else {
                    $('.images').hide();
                }
                if (id === '19' || id === '20' || id === '21' || id === '24') {
                    $('.url').show();
                } else {
                    $('.url').hide();
                }

                if (id !== '19') {
                    $('.jump').hide();
                } else {
                    $('.jump').show();
                }

                if (id === '21') {
                    $('._text').html('活动ID');
                    $('._text2').val('多个ID英文逗号隔开');
                }

                if (url === '0' || url === '1' || url === '2' || url === '9' || url === '10'){
                    $('.url').show();

                    if (url === '2' || url === '1'){
                        $('._text').html('活动id');
                        // $('._text2').val('请填写该营销活动id’)')
                    }
                    if (url === '9'){
                        $('._text').html('填写Url');
                        // $('._text2').val('请输入url链接(http开头)')
                    }
                    if (url === '10'){
                        $('._text').html('填写分类id');
                    }
                }else {
                    $('.url').hide();
                }
            }
        }
    };
    return Controller;
});