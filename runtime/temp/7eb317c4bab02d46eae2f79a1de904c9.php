<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:81:"/www/wwwroot/fywjd.jxsxkeji.com/public/../application/admin/view/index/login.html";i:1591584782;s:71:"/www/wwwroot/fywjd.jxsxkeji.com/application/admin/view/common/meta.html";i:1591584782;s:73:"/www/wwwroot/fywjd.jxsxkeji.com/application/admin/view/common/script.html";i:1591584782;}*/ ?>
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

    <style type="text/css">
        body {
            color: #999;
            /*background:url('<?php echo $background; ?>');*/
            background-size: cover;
        }

        a {
            color: #fff;
        }

        .login-panel {
            margin-top: 150px;
        }

        .login-screen {
            max-width: 400px;
            padding: 0;
            margin: 100px auto 0 auto;
        }

        .login-screen .well {
            border-radius: 3px;
            -webkit-box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 1);
        }

        .login-screen .copyright {
            text-align: center;
        }

        @media(max-width:767px) {
            .login-screen {
                padding: 0 20px;
            }
        }

        .profile-img-card {
            width: 100px;
            height: 100px;
            margin: 10px auto;
            display: block;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
        }

        .profile-name-card {
            text-align: center;
        }

        #login-form {
            margin-top: 20px;
        }

        #login-form .input-group {
            margin-bottom: 28px;
        }

        .login-container {
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            position: relative;
            margin: 0 auto;
            background: url('<?php echo $back; ?>') no-repeat center;
            background-size: cover;
        }

        .login-container,
        .login-weaper {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            width: 100%;
            height: 100%
        }
        .login-border,
        .login-left {
            position: relative;
            min-height: 500px;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex
        }

        .login-left {
            width: 478px;
            height: 100%;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            -webkit-box-shadow: inset -15px 0 10px -15px rgba(1, 64, 86, .3);
            box-shadow: inset -15px 0 10px -15px rgba(1, 64, 86, .3);
            background-image: linear-gradient(130deg, #089ad6, rgba(75, 170, 199, .9), rgba(36, 185, 188, .8), rgba(29, 194, 174, .8), rgba(30, 203, 166, .8), rgba(29, 211, 157, .9), #02ff8f);
            color: #fff;
            float: left;
            position: relative
        }

        .login-left .img {
            width: 100px
        }

        .login-left .log-text {
            width: 140px;
            margin-top: 30px
        }

        .login-time {
            position: absolute;
            left: 58px;
            top: 63px;
            width: 100%;
            color: #fff;
            font-weight: 200;
            font-size: 18px
        }

        .login-left .title {
            margin-top: 200px;
            font-size: 22px
        }

        .login-left .copy-right,
        .login-left .title {
            text-align: center;
            color: #fff;
            font-weight: 300;
            letter-spacing: 2px
        }

        .login-left .copy-right {
            position: absolute;
            bottom: 10px;
            font-size: 12px
        }

        .login-left .logo-title {
            font-size: 32px
        }
        .login-border, .login-main {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }

        .login-border {
            color: #fff;
            width: calc(100% - 578px);
            float: left;
        }
        .login-border {
            color: #fff;
            width: calc(100% - 578px);
            float: left
        }

        .login-border,
        .login-main {
            -webkit-box-sizing: border-box;
            box-sizing: border-box
        }

        .login-main {
            margin: 0 auto;
            width: 458px;
            height: 450px;
            background: hsla(0, 0%, 100%, .8);
            border-radius: 4px;
            -webkit-box-shadow: 0 0 20px rgba(0, 0, 0, .2);
            box-shadow: 0 0 20px rgba(0, 0, 0, .2)
        }

        .login-main>h3 {
            margin-bottom: 20px
        }

        .login-main>p {
            color: #76838f
        }

        .login-title {
            color: #999;
            margin: 32px;
            font-weight: 400;
            font-size: 28px;
            text-align: center
        }

        .login-submit.el-button--small {
            width: 280px;
            height: 50px;
            border-radius: 6px;
            background: #089edb;
            font-size: 18px;
            letter-spacing: 2px;
            font-weight: 300;
            color: #fff;
            cursor: pointer;
            margin-top: 30px;
            font-family: neo;
            -webkit-transition: .25s;
            transition: .25s
        }

        .login-form {
            margin: 10px 0
        }

        .login-form .el-form-item__error {
            left: 100px
        }

        .login-form i {
            color: #d1cfcf
        }

        .login-form .el-form-item__content {
            width: 100%;
            text-align: center
        }

        .login-form .el-form-item {
            margin-bottom: 28px
        }

        .login-form .el-input {
            width: 280px;
            height: 50px;
            margin: 0 auto;
            line-height: 50px;
            border: 1px solid #bfbfbf;
            border-radius: 4px
        }

        .login-form .el-input input {
            width: 240px;
            height: 24px!important;
            line-height: 24px!important;
            margin:13px 44px 13px 0;
            padding-left: 12px;
            background: transparent;
            border: none;
            border-radius: 0;
            border-left: 1px solid #dbd6d6;
            color: #333
        }

        .login-form .el-input .el-input__prefix {
            left: 10px
        }

        .login-form .el-input .el-input__prefix i {
            padding: 0 5px;
            font-size: 22px!important
        }
        .input-group-addon{
            background-color: transparent !important;
            border: none !important;
        }
        .remember-psd{
            padding-left: 70px;
            padding-right: 70px;
        }
        .btn-block{
            width: 280px !important;
            height: 50px !important;
            margin-left: 90px;
            margin-right: 90px;
            background-color: #089edb;
        }
        .btn-block:hover{
            background: #089edb;
            border: none;
        }
    </style>
</head>

<body>
<div class="login-container">
    <div class="login-weaper">
        <div class="login-left">
            <img src="<?php echo $site['logo']; ?>" alt="" class="img">
            <p class="title"><?php echo $site['title']; ?></p>
            <p class="copy-right">版权所有©<?php echo $site['title']; ?> 赣ICP备12000032号</p>
        </div>
        <div class="login-border">
            <div class="login-main">
                <h4 class="login-title">
                    登录
                </h4>
                <div class="el-form login-form">
                    <!--<img id="profile-imgs" class="profile-img-card" src="http://oa.0791jr.com/uploads/20191112/66f8d4984410ef1a73c62717f896d8d4.jpg" />
                    <p id="profile-name" class="profile-name-card"></p>-->

                    <form action="" method="post" id="login-form">
                        <div id="errtips" class="hide"></div>
                        <?php echo token(); ?>
                        <div class="input-group el-input el-input--small el-input--prefix">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></div>
                            <input type="text" class="form-control el-input__inner" id="pd-form-username" placeholder="<?php echo __('Username'); ?>" name="username" autocomplete="off" value="" />
                        </div>

                        <div class="input-group  el-input el-input--small el-input--prefix">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></div>
                            <input type="password" class="form-control  el-input__inner" id="pd-form-password" placeholder="<?php echo __('Password'); ?>" name="password" autocomplete="off" value="" />
                        </div>
                        <?php if($config['fastadmin']['login_captcha']): ?>
                        <div class="input-group  el-input el-input--small el-input--prefix">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span></div>
                            <input type="text" name="captcha" class="form-control  el-input__inner" placeholder="<?php echo __('Captcha'); ?>" data-rule="<?php echo __('Captcha'); ?>:required;length(4)" />
                            <span class="input-group-addon" style="padding:0;border:none;cursor:pointer;">
                                        <img src="<?php echo rtrim('/', '/'); ?>/index.php?s=/captcha" width="100" height="30" onclick="this.src = '<?php echo rtrim('/', '/'); ?>/index.php?s=/captcha&r=' + Math.random();"/>
                                    </span>
                        </div>
                        <?php endif; ?>
                        <input type="hidden" name="keeplogin" id="keeplogin" value="1" />
                        <!--	<div class="form-group remember-psd">
                                <label class="inline" for="keeplogin" style="color: #666;">
                                    <input type="checkbox" name="keeplogin" id="keeplogin" value="1" />
&lt;!&ndash;                                        <?php echo __('记住密码'); ?>&ndash;&gt;
                                    保持登录
                                </label>
                            </div>-->
                        <div class="form-group">
                            <button type="submit" class="btn el-button login-submit el-button--primary el-button--small btn-success btn-lg btn-block" ><?php echo __('Sign in'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
</body>

</html>