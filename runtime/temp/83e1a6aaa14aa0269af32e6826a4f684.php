<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:99:"C:\Users\JAD\Desktop\project\fastadmin\msjt\public/../application/admin\view\cms\channel\index.html";i:1591584782;s:86:"C:\Users\JAD\Desktop\project\fastadmin\msjt\application\admin\view\layout\default.html";i:1591584782;s:83:"C:\Users\JAD\Desktop\project\fastadmin\msjt\application\admin\view\common\meta.html";i:1591584782;s:85:"C:\Users\JAD\Desktop\project\fastadmin\msjt\application\admin\view\common\script.html";i:1591584782;}*/ ?>
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

    <!--<div class="panel-heading">-->
        <!--<?php echo build_heading(null,FALSE); ?>-->
        <!--<ul class="nav nav-tabs" data-field="model_id">-->
            <!--<li class="active"><a href="#t-all" data-value="" data-toggle="tab"><?php echo __('All'); ?></a></li>-->
            <!--<?php if(is_array($modelList) || $modelList instanceof \think\Collection || $modelList instanceof \think\Paginator): if( count($modelList)==0 ) : echo "" ;else: foreach($modelList as $key=>$vo): ?>-->
            <!--<li><a href="#t-<?php echo $vo['id']; ?>" data-value="<?php echo $vo['id']; ?>" data-toggle="tab"><?php echo $vo['name']; ?></a></li>-->
            <!--<?php endforeach; endif; else: echo "" ;endif; ?>-->
        <!--</ul>-->
    <!--</div>-->

    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">
                <div class="widget-body no-padding">
                    <div id="toolbar" class="toolbar">
                        <?php echo build_toolbar('refresh,add,edit,del'); ?>
                        <div class="dropdown btn-group <?php echo $auth->check('cms/channel/multi')?'':'hide'; ?>">
                            <!--<a class="btn btn-primary btn-more dropdown-toggle btn-disabled disabled" data-toggle="dropdown"><i class="fa fa-cog"></i> <?php echo __('More'); ?></a>-->
                            <ul class="dropdown-menu text-left" role="menu">
                                <li><a class="btn btn-link btn-multi btn-disabled disabled" href="javascript:;" data-params="status=normal"><i class="fa fa-eye"></i> <?php echo __('Set to normal'); ?></a></li>
                                <li><a class="btn btn-link btn-multi btn-disabled disabled" href="javascript:;" data-params="status=hidden"><i class="fa fa-eye-slash"></i> <?php echo __('Set to hidden'); ?></a></li>
                            </ul>
                        </div>
                        <?php if($auth->check('cms/channel/admin')): ?>
                        <a class="btn btn-danger btn-dialog" href="<?php echo url('cms.channel/admin'); ?>" title="<?php echo __('栏目授权'); ?>"><i class="fa fa-cog"></i> <?php echo __('栏目授权'); ?></a>
                        <?php endif; ?>
                    </div>
                    <table id="table" class="table table-striped table-bordered table-hover" 
                           data-operate-edit="<?php echo $auth->check('cms/channel/edit'); ?>"
                           data-operate-del="<?php echo $auth->check('cms/channel/del'); ?>" 
                           width="100%">
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>