define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {
            // console.log(Orderdata)
            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echarts'));
            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '订单金额统计图(元)',
                    subtext: '',
                    textStyle: {
                        color: '#333333',
                        fontWeight: 'normal',
                        fontSize: 16,
                    }

                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['订单总金额','已支付金额']
                },
                toolbox: {},
                xAxis: [
                    {
                        type: 'category',
                        boundaryGap: false,
                        data: Orderdata.data,
                    }
                ],
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                grid: [{
                    left: '50',
                    top: '50',
                    right: '20',
                    bottom: 100
                }],
                series: [
                    {
                        name: '订单总金额',
                        type: 'line',
                        color: ['#f6162c'],
                        smooth: true,
                        data: Orderdata.sucess,
                    },
                    {
                        name: '已支付金额',
                        type: 'line',
                        color: ['#4845c0'],
                        smooth: true,
                        data: Orderdata.sucess2,
                    },

                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);

            var myChart2 = Echarts.init(document.getElementById('echarts2'));
            // 指定图表的配置项和数据
            var option2 = {

                title: {
                    text: '订单数量统计图(笔)',
                    subtext: '',
                    textStyle: {
                        color: '#333333',
                        fontWeight: 'normal',
                        fontSize: 16,
                    }

                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['订单总数量','已支付订单数量']
                },
                toolbox: {},
                xAxis: [
                    {
                        type: 'category',
                        boundaryGap: false,
                        data: Orderdata.data,
                    }
                ],
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                grid: [{
                    left: '50',
                    top: '50',
                    right: '20',
                    bottom: 100
                }],

                // backgroundColor: 'rgba(181,151,34,0.51)',背景色
                series: [
                    {
                        name: '订单总数量',
                        type: 'line',
                        color: ['#f6162c'],
                        smooth: true,
                        // itemStyle: {normal: {areaStyle: {type: 'default'}}}, //背景色
                        data: Orderdata.sum,
                    },
                    {
                        name: '已支付订单数量',
                        type: 'line',
                        color: ['#4845c0'],
                        smooth: true,
                        data: Orderdata.sum2,
                    },

                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            myChart2.setOption(option2);
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.statistice_url,
                pk: 'id',
                sortName: 'visit.id',
                columns: [
                    [
                        {checkbox: true}, // 是否展示全选功能 true 展示  false 隐藏
                        {field: 'id', title: __('Id'), sortable: true, operate:false,visible:false}, // id 跟数据库对应字段   __('Id') 配置文件字段  sortable 是否需要根据某个字段排序  true 需要  false 不需要
                        {
                            field: 'sdfsadf', title: '序号'
                            , formatter: function (value, row, index) {
                                return index + 1;
                            }
                            , operate: false
                        },
                        {field: 'user.avatar', title:'头像',formatter: Table.api.formatter.image, operate: false},
                        {field: 'user.nickname', title: '昵称', operate: 'LIKE'},
                        {field: 'user.mobile', title: '手机号', operate: 'LIKE'},
                        // {field: 'address', title: '地址', operate: false},
                        {
                            field: 'create_time',
                            title: '访问时间',
                            operate: false,
                            addclass: 'datetimerange',
                            operate: 'RANGE',
                            formatter: Table.api.formatter.datetime
                        },
                        // {
                        //     field: 'operate',
                        //     title: __('Operate'),
                        //     table: table,
                        //     events: Table.api.events.operate,
                        //     formatter: Table.api.formatter.operate
                        // }
                    ]
                ]
            });
            Table.api.bindevent(table);


        },
        user: function () {

            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echarts'));
            // 指定图表的配置项和数据
            var option = {

                title: {
                    text: '用户统计',
                    subtext: '',
                    textStyle: {
                        fontWeight: 'normal',
                        fontSize: 18,

                    }

                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['注册人数', '访问人数']
                },
                toolbox: {},
                xAxis: [
                    {
                        type: 'category',
                        boundaryGap: false,
                        data: Orderdata.data,
                    }
                ],
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                grid: [{
                    left: '30',
                    top: '50',
                    right: '20',
                    bottom: '70'
                }],
                //['#c23531','#2f4554', '#61a0a8', '#d48265', '#91c7ae','#749f83',  '#ca8622', '#bda29a','#6e7074', '#546570', '#c4ccd3']
                series: [
                    {
                        name: '注册人数',
                        type: 'line',
                        color: ['#36bf36'],
                        smooth: true,
                        // itemStyle: {normal: {areaStyle: {type: 'default'}}},
                        data: Orderdata.reg,
                    },
                   /* {
                        name: '访问人数',
                        type: 'line',
                        color: ['#c23531'],
                        smooth: true,
                        // itemStyle: {normal: {areaStyle: {type: 'default'}}},
                        data: Orderdata.login,
                    },*/
                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
            // 基于准备好的dom，初始化echarts实例
            var myChart11 = Echarts.init(document.getElementById('echarts11'), 'light');
            // 指定图表的配置项和数据
            option11 = {
                title: {
                    text: '今日访客数',
                    // subtext: '纯属虚构',
                    left: 'center'

                },
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b} : {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    // data: ['老用户访问量', '新用户访问量']
                },
                series: [
                    {
                        name: '访问来源',
                        type: 'pie',
                        radius: '55%',
                        center: ['50%', '60%'],
                        data: [
                            // {value: 335, name: '直接访问'},
                            // {value: 310, name: '邮件营销'},
                            // {value: 234, name: '联盟广告'},
                            {value: Orderdata.oldvisit, name: '老用户访客数'},
                            {value: Orderdata.newvisit, name: '新用户访客数'}
                        ],
                        itemStyle: {
                            normal: {
                                color: function (params) {
                                    var colorList = ['#0693DC', '#31CFFD'];
                                    return colorList[params.dataIndex];
                                }
                            }
                        }
                        // emphasis: {
                        //     itemStyle: {
                        //         shadowBlur: 10,
                        //         shadowOffsetX: 0,
                        //         // color:['#0693DC'],
                        //         shadowColor: 'rgb(26,163,236)'
                        //     }
                        // }
                    }
                ]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart11.setOption(option11);
            var myChart22 = Echarts.init(document.getElementById('echarts22'));
            // 指定图表的配置项和数据
            option22 = {
                title: {
                    text: '本月访客数',
                    // subtext: '纯属虚构',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b} : {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    // data: ['本月老用户访问量', '本月新用户访问量']
                },
                series: [
                    {
                        name: '访问来源',
                        type: 'pie',
                        radius: '55%',
                        center: ['50%', '60%'],
                        data: [
                            {value: Orderdata.oldvisitmonth, name: '本月老用户访客数'},
                            {value: Orderdata.newvisitmonth, name: '本月新用户访客数'}
                        ],
                        itemStyle: {
                            normal: {
                                color: function (params) {
                                    var colorList = [ '#0693DC','#31CFFD'];
                                    return colorList[params.dataIndex];
                                }
                            }
                        }
                        // emphasis: {
                        //     itemStyle: {
                        //         shadowBlur: 10,
                        //         shadowOffsetX: 0,
                        //         shadowColor: 'rgba(0,0,0,0.5)'
                        //     }
                        // }
                    }
                ]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart22.setOption(option22);
            // var myChart33 = Echarts.init(document.getElementById('echarts33'));
            // // 指定图表的配置项和数据
            // option33 = {
            //     title: {
            //         text: '本年访客数',
            //         // subtext: '纯属虚构',
            //         left: 'center'
            //     },
            //     tooltip: {
            //         trigger: 'item',
            //         formatter: '{a} <br/>{b} : {c} ({d}%)'
            //     },
            //     legend: {
            //         orient: 'vertical',
            //         left: 'left',
            //         // data: ['老用户访问量', '新用户访问量']
            //     },
            //     series: [
            //         {
            //             name: '访问来源',
            //             type: 'pie',
            //             radius: '55%',
            //             center: ['50%', '60%'],
            //             data: [
            //                 {value: Orderdata.oldvisityear, name: '老用户访客数'},
            //                 {value: Orderdata.newvisityear, name: '新用户访客数'}
            //             ],
            //             itemStyle: {
            //                 normal: {
            //                     color: function (params) {
            //                         var colorList = [ '#31CFFD','#0693DC'];
            //                         return colorList[params.dataIndex];
            //                     }
            //                 }
            //             }
            //
            //         }
            //     ]
            // };
            //
            // // 使用刚指定的配置项和数据显示图表。
            // myChart33.setOption(option33);


            //表格
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    statistice_url: 'Statistics/user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.statistice_url,
                pk: 'id',
                sortName: 'visit.id',
                columns: [
                    [
                        {checkbox: true}, // 是否展示全选功能 true 展示  false 隐藏
                        {field: 'id', title: __('Id'), sortable: true, operate:false,visible:false}, // id 跟数据库对应字段   __('Id') 配置文件字段  sortable 是否需要根据某个字段排序  true 需要  false 不需要
                        {
                            field: 'sdfsadf', title: '序号'
                            , formatter: function (value, row, index) {
                                return index + 1;
                            }
                            , operate: false
                        },
                        {field: 'user.avatar', title:'头像',formatter: Table.api.formatter.image, operate: false},
                        {field: 'user.nickname', title: '昵称', operate: 'LIKE'},
                        {field: 'user.mobile', title: '手机号', operate: 'LIKE'},
                        // {field: 'address', title: '地址', operate: false},
                        {
                            field: 'create_time',
                            title: '访问时间',
                            operate: false,
                            addclass: 'datetimerange',
                            operate: 'RANGE',
                            formatter: Table.api.formatter.datetime
                        },
                        // {
                        //     field: 'operate',
                        //     title: __('Operate'),
                        //     table: table,
                        //     events: Table.api.events.operate,
                        //     formatter: Table.api.formatter.operate
                        // }
                    ]
                ]
            });
            Table.api.bindevent(table);
            //*************************** 自定义export开始
            var submitForm = function (ids, layero) {
                var options = table.bootstrapTable('getOptions');

                var columns = [];
                $.each(options.columns[0], function (i, j) {
                    if (j.field && !j.checkbox && j.visible && j.field != 'operate') {
                        columns.push(j.field);
                    }
                });
                var search = options.queryParams({});
                $("input[name=search]", layero).val(options.searchText);
                $("input[name=ids]", layero).val(ids);
                $("input[name=filter]", layero).val(search.filter);
                $("input[name=op]", layero).val(search.op);
                $("input[name=columns]", layero).val(columns.join(','));
                $("form", layero)[0].submit();
            };

            /*$(document).on('click', 'btn-selected', function () {
                console.log($(this).data('checked'));
                if ($(this).data('checked')) {
                    $(this).data('checked', true)
                    $('.ptitle input').prop('checked', true);
                } else {
                    $(this).data('checked', false)
                    $('.ptitle input').prop('checked', false);
                }
            });*/

            $(document).on("click", ".btn-export", function () {
                var ids = Table.api.selectedids(table);
                var page = table.bootstrapTable('getData');
                var all = table.bootstrapTable('getOptions').totalRows;

                Layer.confirm("请选择导出的选项<form action='" + Fast.api.fixurl("Statistics/out") + "' method='post' target='_blank'><input type='hidden' name='ids' value='' /><input type='hidden' name='filter' ><input type='hidden' name='op'><input type='hidden' name='search'><input type='hidden' name='columns'></form>", {
                    title: '导出数据',
                    btn: ["选中项(" + ids.length + "条)", "本页(" + page.length + "条)", "全部(" + all + "条)"],
                    success: function (layero, index) {
                        $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
                    }
                    , yes: function (index, layero) {
                        submitForm(ids.join(","), layero);
                        return false;
                    }
                    ,
                    btn2: function (index, layero) {
                        var ids = [];
                        $.each(page, function (i, j) {
                            ids.push(j.goods_id);
                        });
                        submitForm(ids.join(","), layero);
                        return false;
                    }
                    ,
                    btn3: function (index, layero) {
                        submitForm("all", layero);
                        return false;
                    }
                });

                //关闭弹窗  刷新页面
                $(document).on("click", ".layui-layer-btn0", function () {
                    Layer.closeAll();
                    $(".btn-refresh").trigger("click");
                });
            });

            //*************************** 自定义export结束

        },
        goods: function () {

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'statistics/goods',
                    dragsort_url: '',
                    table: 'litestoregoods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'id',
                pk: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'goods_id', title: '商品id', visible: false, operate: false},
                        {
                            field: 'sdfsadf', title: '序号'
                            , formatter: function (value, row, index) {
                                return index + 1;
                            }
                            , operate: false
                        },
                        {field: 'name', title: '商品名称', operate: "like"},
                        {field: 'type.name', title: '商品分类', operate: false},
                        // {field: 'units', title: __('单位'), operate: false},
                        {field: 'goods_count', title: '订单量', operate: false},
                        {field: 'user_count', title: '成交用户量', operate: false},
                        // {field: 'vip_price', title: '商品vip价格', operate: false},

                        {
                            field: 'sales_actual',
                            title: '销量',
                            formatter: function (value, row, index) {
                                return value > 0 ? value : 0;
                            },
                            operate: false
                        },
                        {
                            field: 'sales_price',
                            title: '销售额',
                            formatter: function (value, row, index) {
                                return value > 0 ? value : 0;
                            },
                            operate: false
                        },
                        //启用时间段搜索
                        {
                            field: 'createtime',
                            title: '选择时间',
                            formatter: Table.api.formatter.datetime,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            data: 'autocomplete="off"',
                            visible: false
                        },
                    ]
                ]
            });

            //*************************** 自定义export开始
            var submitForm = function (ids, layero) {
                var options = table.bootstrapTable('getOptions');

                var columns = [];
                $.each(options.columns[0], function (i, j) {
                    if (j.field && !j.checkbox && j.visible && j.field != 'operate') {
                        columns.push(j.field);
                    }
                });
                var search = options.queryParams({});
                $("input[name=search]", layero).val(options.searchText);
                $("input[name=ids]", layero).val(ids);
                $("input[name=filter]", layero).val(search.filter);
                $("input[name=op]", layero).val(search.op);
                $("input[name=columns]", layero).val(columns.join(','));
                $("form", layero).submit();
            };

            /*$(document).on('click', 'btn-selected', function () {
                console.log($(this).data('checked'));
                if ($(this).data('checked')) {
                    $(this).data('checked', true)
                    $('.ptitle input').prop('checked', true);
                } else {
                    $(this).data('checked', false)
                    $('.ptitle input').prop('checked', false);
                }
            });*/


            $(document).on("click", ".btn-export", function () {
                var ids = Table.api.selectedids(table);
                var page = table.bootstrapTable('getData');
                var all = table.bootstrapTable('getOptions').totalRows;


                Layer.confirm("请选择导出的选项<form action='" + Fast.api.fixurl("statistics/goods_out") + "' method='post' target='_blank'><input type='hidden' name='ids' value='' /><input type='hidden' name='filter' ><input type='hidden' name='op'><input type='hidden' name='search'><input type='hidden' name='columns'></form>", {
                    title: '导出数据',
                    btn: ["选中项(" + ids.length + "条)", "本页(" + page.length + "条)", "全部(" + all + "条)"],
                    success: function (layero, index) {
                        $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
                    }
                    , yes: function (index, layero) {
                        submitForm(ids.join(","), layero);
                        return false;
                    }
                    ,
                    btn2: function (index, layero) {

                        var ids = [];
                        $.each(page, function (i, j) {
                            ids.push(j.id);
                        });
                        submitForm(ids.join(","), layero);
                        return false;
                    }
                    ,
                    btn3: function (index, layero) {
                        submitForm("all", layero);
                        return false;
                    }
                });

                //关闭弹窗  刷新页面
                $(document).on("click", ".layui-layer-btn0", function () {
                    Layer.closeAll();
                    $(".btn-refresh").trigger("click");
                });
            });

            //*************************** 自定义export结束
            // 为表格绑定事件
            Table.api.bindevent(table);

        }
    }
    return Controller;
});