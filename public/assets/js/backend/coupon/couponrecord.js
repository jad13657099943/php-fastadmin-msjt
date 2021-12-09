define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {


            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'coupon/couponrecord/index',
                    del_url: 'coupon/couponrecord/del',
                    add_url: 'coupon/couponrecord/add',
                    multi_url: 'coupon/couponrecord/multi',
                    table: 'couponrecord',
                }
            });

            var table = $("#table");
             // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'couponrecord.id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'coupon.title', title: __('Title'), align: 'left',operate:'LIKE'},
                        {field: 'coupon.coupon_price', title: __('Couponprice'), align: 'left'},
                        {field: 'user.username', title: __('Couponuname'), align: 'left',operate:false},
                        {field: 'create_time', title: __('Createtime'), formatter: Table.api.formatter.datetime, operate: false},
                        {field: 'coupon.starttime', title: __('Starttime'), formatter: Table.api.formatter.datetime, operate:false,  sortable: true},
                        {field: 'coupon.endtime', title: __('Endtime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange'},
                        {field: 'status_text', title: __('Status'),operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        add: function () {
            Form.api.bindevent("form");
            //console.log($('.spec_add_bth'))
            $(document).on('click','.spec_add_btn', function (event) {
                var that=$(this);
                var url = $(this).attr('data-url');

                //console.log(url);
                if(!url) return false;
                var msg = $(this).attr('data-title');
                var width = $(this).attr('data-width');
                var height = $(this).attr('data-height');
                var area = [$(window).width() > 800 ? (width?width:'800px') : '95%', $(window).height() > 600 ? (height?height:'600px') : '95%'];
                var options = {
                    shadeClose: false,
                    shade: [0.3, '#393D49'],
                    area: area,
                    callback:function(data){
                        that.parent().find('.week').html(data.url);
                        that.parent().find('.weekc').val(data.url);
                        that.parent().find('.week').show();
                    }
                };

                Fast.api.open(url,msg,options);
            });

            //切换显示隐藏变量字典列表
            $(document).on("change", "form#add-form select[name='row[type]']", function (e) {
                $("#add-content-container").toggleClass("hide", ['select', 'selects', 'checkbox', 'radio'].indexOf($(this).val()) > -1 ? false : true);
            });

            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        select: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'coupon/couponrecord/select',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pagination: false,
                commonSearch:true,
                showToggle: false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('ID')},
                        {field: 'mobile', title: __('mobile'),operate:'='},
                        {field: 'username', title: __('username'),operate:'LIKE'},
                        {
                            field: 'operate', title: __('Operate'), events: {
                                'click .btn-chooseone': function (e, value, row, index) {
                                    var multiple = Backend.api.query('multiple');
                                    multiple = multiple == 'true' ? true : false;
                                    row.url=row.id;
                                    Fast.api.close(row);
                                },
                            }, formatter: function (value,row) {
                                return '<a href="javascript:;" class="btn btn-danger btn-chooseone btn-xs"><i class="fa fa-check"></i> ' + __('Choose') + '</a>';
                            }
                        }

                    ]
                ]
            });


            // 选中多个
            $(document).on("click", ".btn-choose-multi", function () {
                var urlArr = new Array();
                $.each(table.bootstrapTable("getAllSelections"), function (i, j) {
                    urlArr.push(j.id);
                });
               // console.log(urlArr.length);
                var multiple = Backend.api.query('multiple');
                multiple = multiple == 'true' ? true : false;
                Fast.api.close({url: urlArr.join(","), multiple: true});
            });


            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
