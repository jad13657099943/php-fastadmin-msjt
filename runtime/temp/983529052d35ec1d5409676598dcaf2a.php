<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:100:"/www/wwwroot/fywjd.jxsxkeji.com/public/../application/admin/view/litestore/litestoreorder/index.html";i:1616564491;s:74:"/www/wwwroot/fywjd.jxsxkeji.com/application/admin/view/layout/default.html";i:1591584782;s:71:"/www/wwwroot/fywjd.jxsxkeji.com/application/admin/view/common/meta.html";i:1591584782;s:73:"/www/wwwroot/fywjd.jxsxkeji.com/application/admin/view/common/script.html";i:1591584782;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<link rel="shortcut icon" href="<?php echo $site['logo']; ?>"/>
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>"
      rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
<script src="/assets/js/html5shiv.js"></script>
<script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config: <?php echo json_encode($config); ?>};
</script>
    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !$config['fastadmin']['multiplenav']): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <div class="panel panel-default panel-intro">
    <div class="panel-heading">
        <!--  <?php echo build_heading(null,FALSE); ?>-->

        <ul class="nav nav-tabs" data-field="status">
            <ul class="nav nav-tabs" data-field="status">
                <li class="active"><a href="#" data-value="" data-toggle="tab"><?php echo __('All'); ?>(<?php echo $row['total_number']; ?>)</a>
                </li>
                <li><a href="#" data-order_status="10" data-toggle="tab">待付款(<?php echo $row['no_pay_number']; ?>)</a></li>
                <li><a href="#" data-order_status="20" data-toggle="tab">待发货(<?php echo $row['no_send_goods_number']; ?>)</a></li>
                <li><a href="#" data-order_status="30" data-toggle="tab">待收货(<?php echo $row['no_take_over_number']; ?>)</a></li>
                <li><a href="#" data-order_status="70" data-toggle="tab">待自提(<?php echo $row['no_mention']; ?>)</a></li>
                <li><a href="#" data-order_status="40" data-toggle="tab">待评价(<?php echo $row['no_evaluate_number']; ?>)</a></li>
                <li><a href="#" data-order_status="50" data-toggle="tab">已完成(<?php echo $row['finish_number']; ?>)</a></li>
                <li><a href="#" data-order_status="0" data-toggle="tab">已取消(<?php echo $row['cancel_number']; ?>)</a></li>
            </ul>
        </ul>
    </div>

    <div class="panel-body">
        <div class="tab-content">
            <div class="tab-pane fade active in">
                <!--        <div id="myTabContent" class="tab-content">-->
                <!--            <div class="tab-pane fade active in" id="one">-->
                <div class="widget-body no-padding">
                    <div id="toolbar" class="toolbar">

                        <!--                        <a href="javascript:;" class="btn btn-success btn-selected" title="全选"><i-->
                        <!--                                class="fa fa-upload"></i>全选</a>-->
                        <?php echo build_toolbar('refresh'); ?>
                        <a href="javascript:;"
                           class="btn btn-success  btn-export <?php echo $auth->check('litestore/litestoreorder/out')?'':'hide'; ?>"
                           title="<?php echo __('Export'); ?>" id="btn-export-file"><i class="fa fa-upload"></i> <?php echo __('Export'); ?></a>
                        <a href="javascript:;"
                           class="btn btn-success btn-print <?php echo $auth->check('/admin/litestore/litestoreorder/prints')?'':'hide'; ?>"
                           title="<?php echo __('批量打印'); ?>" id="btn-print-file"><i class="fa fa-upload"></i> <?php echo __('批量打印'); ?></a>
<!--                        <a href="javascript:;"-->
<!--                           class="btn btn-success btn-qrcode <?php echo $auth->check('/admin/litestore/litestoreorder/prints')?'':'hide'; ?>"-->
<!--                           title="<?php echo __('自提码'); ?>" id="btn-qrcode-file"><i class="fa fa-upload"></i> <?php echo __('自提码'); ?></a>-->
                    </div>

                    <table id="table" class="table table-striped table-bordered table-hover table-nowrap"
                           data-operate-edit="<?php echo $auth->check('litestore/litestoreorder/edit'); ?>"
                           data-operate-del="<?php echo $auth->check('litestore/litestoreorder/del'); ?>"
                           width="100%">
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<style type="text/css">
    /*.example {
        height:100%;position: relative;
    }
    .example > span {
        position:absolute;left:15px;top:15px;
    }*/
    .order {
        width: 93vw;
        margin: 12px auto;
    }

    .flex {
        display: flex;
    }

    .red {
        color: red;
    }

    .order {
        min-width: 600px;
        margin-bottom: 10px;
    }

    .table-bordered {
        border: 1px solid rgba(0, 0, 0, .1);
        padding: 10px 20px;
    }

    .img-box {
        width: 100px;
        height: 100px;
        overflow: hidden;
        position: relative;
        margin: 10px 0;
        margin-right: 20px;
    }

    .pic {
        margin: 10px 0;
        position: relative;
    }

    .img-box img {
        max-height: 150px;
        max-width: 150px;
        position: absolute;
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%, -50%);
        -moz-transform: translate(-50%, -50%);
        -ms-transform: translate(-50%, -50%);
        -o-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
    }

    .guide {
        padding-top: 10px;
    }

    .guide .red {
        margin-right: 10px;
    }

    .ptitle {
        align-items: center;
        color: gray;
    }

    .black {
        color: black;
    }

    .ptitle span {
        margin-right: 10px;
    }

    .label {
        background: darkgray;
        color: white;
        line-height: 20px;
        padding: 6px 8px;
        border-right: 3px;
        margin-right: 10px;
    }

    .pinfo {
        color: gray;
    }

    .pinfo .black {
        margin-right: 15px;
    }

    .pinfo div {
        margin-bottom: 10px;
    }

    .total {
        position: absolute;
        top: 50%;
        /*max-left: 600px;*/
        left: 50%;
        /*width: 750px;*/
        width: 50%;
        -webkit-transform: translateY(-50%);
        -moz-transform: translateY(-50%);
        -ms-transform: translateY(-50%);
        -o-transform: translateY(-50%);
        transform: translateY(-50%);
        align-items: center;
    }

    .totalmoney {
        width: 70%;
        height: 50px;
    }


    .labels {

        color: white;
        line-height: 20px;
        padding: 6px 8px;
        border-right: 3px;
        margin-right: 10px;
    }

    .dis {
        width: 30%;
    }

    .type .flex {
        align-items: center;
    }

    .zftype {
        background: #ccc;
    }

    .payed {
        background: #39CCCC;
    }

    .nosend {
        background: #0C0C0C;
    }

    .ps {
        background: #18bc9c;
    }

    .pss {
        background: red;
    }
    .zt {
        background: #2ae300;
    }

    .type {
        flex: 1;
        min-width: 210px;
    }

    .row {
        justify-content: center;
    }

    .order input {

    }

    .fixed-table-container input[type=checkbox] {
        margin: 0 10px !important;
    }
</style>

<script id="itemtpl" type="text/html">
    <!--
    如果启用了templateView,默认调用的是itemtpl这个模板，可以通过设置templateFormatter来修改
    在当前模板中可以使用三个变量(item:行数据,i:当前第几行,data:所有的行数据)
    此模板引擎使用的是art-template的native,可参考官方文档
    -->

    <div>
        <!--下面四行是为了展示随机图片和标签，可移除-->
        <% var imagearr = ['https://ws2.sinaimg.cn/large/006tNc79gy1fgphwokqt9j30dw0990tb.jpg',
        'https://ws2.sinaimg.cn/large/006tNc79gy1fgphwt8nq8j30e609f3z4.jpg',
        'https://ws1.sinaimg.cn/large/006tNc79gy1fgphwn44hvj30go0b5myb.jpg',
        'https://ws1.sinaimg.cn/large/006tNc79gy1fgphwnl37mj30dw09agmg.jpg',
        'https://ws3.sinaimg.cn/large/006tNc79gy1fgphwqsvh6j30go0b576c.jpg']; %>
        <% var image = imagearr[item.id % 5]; %>
        <% var labelarr = ['primary', 'success', 'info', 'danger', 'warning']; %>
        <% var label = labelarr[item.id % 5]; %>
        <div>
            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="one">

                    <div class="order table table-striped table-bordered table-hover table-nowrap" id="t-all">
                        <div style="background-color: #f5f5f5; border-color: #ddd;height:40px;line-height:40px;overflow:hidden;  ">

                            <div class="flex ptitle" style="margin-top: 5px">
                                <input name="checkbox" data-id="<%=item.id%>" type="checkbox"/>
                                <span class="porder">ID：<span class="black"><%=item.id%></span></span>
                                <span class="porder">订单编号：<span class="black"><%=item.order_no%></span></span>
                                <span class="username">用户昵称:<span class="black"><%=item.nickname%></span></span>
                                <span class="username">用户电话:<span class="black"><%=item.mobile%></span></span>
                                <span class="username">下单时间:<span class="black"><%=item.createtime%></span></span>
                                <%if(item.pay_time > 0){%>
                                <span class="username">支付时间: <span class="black"><%=Moment(item.pay_time*1000).format("YYYY-MM-DD HH:mm:ss")%></span></span>
                                <%}%>
                            </div>
                            <hr style="border:0.5px double #e8e8e8"/>
                        </div>
                        <div class="pic flex">
                            <div class="list">
                                <% for(var i=0;i
                                <item.goods.length ;i++){%>
                                    <div class="product flex">
                                        <div class="img-box">
                                            <img src="<%=item['goods'][i]['images']%>" alt="">
                                        </div>
                                        <div class="guide">
                                            <p><%=item['goods'][i]['goods_name']%></p>
                                            <%if(item['goods'][i]['key_name']){%><p>规格：<span class="red"><%=item['goods'][i]['key_name']%></span><%}%>
                                            <p>商城价格：<span class="red">￥<%=item['goods'][i]['goods_price']%></span> </p>
                                            <span>数量：</span><span class="red"><%=item['goods'][i]['total_num']%>件</span>
                                        </p>
                                            <p>小计：<span class="red">￥<%=item['goods'][i]['total_price']%></span>
                                            </p>
<!--                                            <p>状态：<span >-->
<!--                                                    <%if(item['goods'][i]['is_refund'] == '0'){%>正常-->
<!--                                                    <%}else if(item['goods'][i]['is_refund'] == '1'){%>退款中-->
<!--                                                    <%}else if(item['goods'][i]['is_refund'] == '2'){%>退款完成-->
<!--                                                    <%}else if(item['goods'][i]['is_refund'] == '4'){%>退款被拒-->
<!--                                                    <%}else{%>退款失败-->
<!--                                                    <%}%>-->
<!--                                                    </span>-->
<!--                                            </p>-->
                                        </div>
                                    </div>

                                    <%}%>

                            </div>
                            <div class="total flex">

                                <div class="totalmoney">
                                    总金额：<%=item.total_price%>（运费<%=item.express_price%>,优惠卷使用<%=item.coupon_price%>,积分使用<%=item.use_integral%>,余额使用<%=item.use_money%>,支付金额<%=item.pay_price%>）
                                </div>

                                <div class="dis">
<!--                                    <span class="label"><%=item.is_status_text%></span>-->
                                    <span class="label ps btn-dialog" data-title="查看详情"
                                          href="/admin/litestore/litestoreorder/detail/type/1/ids/<%=item.id%>"
                                          style="cursor: pointer"><i class="fa fa-eye"></i> 订单详情</span>

                                    <!--<span class="label pss btn-dialog" data-title="查看详情"
                                          href="/admin/litestore/litestoreorder/del/ids/<%=item.id%>"
                                          style="cursor: pointer"><i class="fa fa-eye"></i> 删除</span>-->
                                </div>

                                <%if(item.order_status==20 && item.refund_status != 10){%>
                                <p class="flex">
                                    <span class="label ps btn-dialog"
                                          href="/admin/litestore/litestoreorder/detail/type/2/ids/<%=item.id%>"
                                          style="cursor: pointer">确认发货</span>
<!--                                    <span style="color: #18bc9c;" class="labels"><%=item.order_status_text%></span>-->
                                </p>
                                <%}else if(item.order_status==30 && item.refund_status != 10){%>
                                    <p class="flex">
                                        <span class="label pss btn-dialog"
                                              href="/admin/litestore/litestoreorder/refund/ids/<%=item.id%>"
                                              style="cursor: pointer">退款</span>
                                    </p>
                                <%if(item.is_status==2){%>
                                <p class="flex">
                                        <span class="label pss btn-dialog"
                                              href="/admin/litestore/litestoreorder/qrcode/ids/<%=item.id%>"
                                              style="cursor: pointer">提货码</span>
                                </p>
                                <%}%>
                                <%}%>
                                <%if(item.order_status==20 && item.refund_status != 10){%>
                                <p class="flex">
                                    <span class="label ps btn-dialog"
                                          href="/admin/litestore/litestoreorder/prints/ids/<%=item.id%>" style="cursor: pointer; background-color: #0d90ff">打印</span>
                                </p>
                                <%}%>
                                <!--<p class="flex">
                                    <a href="/admin/litestore/litestoreorder/del/ids/<%=item.id%>" class="btn btn-danger btn-del btn-delone" data-id="<%=item.id%>"><i class="fa fa-trash"></i> 删除</a>
                                </p>-->
                            </div>
                        </div>
                        <div class="pinfo">
                            <div class="flex">
                                <%if(item.is_status==1){%><span>收货人：<span
                                    class="black"><%=item.name%></span></span><%}else{%><span>收货人：<span
                                    class="black"> <%=item.consignee%></span></span><%}%>

                                <span>联系电话:<span class="black"><%if(item.is_status==1){%><%=item.phone%><%}else{%> <%=item.reserved_telephone%><%}%></span></span>
                                <%if(item.is_status==1){%>
                                <span>地址:<span class="black"><%=item.site%></span></span>
                                <%}%>

                            </div>

                        </div>
                    </div>


                    <!--table-->
                </div>

            </div>


        </div>
    </div>
</script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>