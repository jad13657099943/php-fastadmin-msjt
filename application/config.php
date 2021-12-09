<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Env;

error_reporting(E_ERROR | E_PARSE);
return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------
    // 应用命名空间
    'app_namespace' => 'app',
    // 应用调试模式
    'app_debug' => Env::get('app.debug', true),
    // 应用Trace
    'app_trace' => Env::get('app.trace', false),
    // 应用模式状态
    'app_status' => '',
    // 是否支持多模块
    'app_multi_module' => true,
    // 入口自动绑定模块
    'auto_bind_module' => false,
    // 注册的根命名空间
    'root_namespace' => [],
    // 扩展函数文件
    'extra_file_list' => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type' => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return' => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler' => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler' => 'callback',
    // 默认时区
    'default_timezone' => 'PRC',
    // 是否开启多语言
    'lang_switch_on' => true,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter' => '',
    // 默认语言
    'default_lang' => 'zh-cn',
    // 应用类库后缀
    'class_suffix' => false,
    // 控制器类后缀
    'controller_suffix' => false,
    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------
    // 默认模块名
    'default_module' => 'admin',
    // 禁止访问模块
    'deny_module_list' => ['common'],
    // 默认控制器名
    'default_controller' => 'Index',
    // 默认操作名
    'default_action' => 'index',
    // 默认验证器
    'default_validate' => '',
    // 默认的空控制器名
    'empty_controller' => 'Error',
    // 操作方法后缀
    'action_suffix' => '',
    // 自动搜索控制器
    'controller_auto_search' => true,
    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------
    // PATHINFO变量名 用于兼容模式
    'var_pathinfo' => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch' => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr' => '/',
    // URL伪静态后缀
    'url_html_suffix' => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param' => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type' => 0,
    // 是否开启路由
    'url_route_on' => true,
    // 路由使用完整匹配
    'route_complete_match' => false,
    // 路由配置文件（支持配置多个）
    'route_config_file' => ['route'],
    // 是否强制使用路由
    'url_route_must' => false,
    // 域名部署
    'url_domain_deploy' => false,
    // 域名根，如thinkphp.cn
    'url_domain_root' => 'http://msjt.jxsxkeji.com/',
    // 是否自动转换URL中的控制器和操作名
    'url_convert' => true,
    // 默认的访问控制器层
    'url_controller_layer' => 'controller',
    // 表单请求类型伪装变量
    'var_method' => '_method',
    // 表单ajax伪装变量
    'var_ajax' => '_ajax',
    // 表单pjax伪装变量
    'var_pjax' => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache' => false,
    // 请求缓存有效期
    'request_cache_expire' => null,
    //七牛云域名
    'item_url' => '',
    //商品详情默认发货地址
    'default_fh_address' => '江西.南昌',
    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------
    'template' => [
        // 模板引擎类型 支持 php think 支持扩展
        'type' => 'Think',
        // 模板路径
        'view_path' => '',
        // 模板后缀
        'view_suffix' => 'html',
        // 模板文件名分隔符
        'view_depr' => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin' => '{',
        // 模板引擎普通标签结束标记
        'tpl_end' => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end' => '}',
        'tpl_cache' => true,
    ],
    // 视图输出字符串内容替换,留空则会自动进行计算
    'view_replace_str' => [
        '__PUBLIC__' => '',
        '__ROOT__' => '',
        '__CDN__' => '',
    ],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => APP_PATH . 'common' . DS . 'view' . DS . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl' => APP_PATH . 'common' . DS . 'view' . DS . 'tpl' . DS . 'dispatch_jump.tpl',
    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------
    // 异常页面的模板文件
    'exception_tmpl' => APP_PATH . 'common' . DS . 'view' . DS . 'tpl' . DS . 'think_exception.tpl',
    // 错误显示信息,非调试模式有效
    'error_message' => '你所浏览的页面暂时无法访问',
    // 显示错误信息
    'show_error_msg' => true,
    // 异常处理handle类 留空使用 \think\exception\Handle
    //\app\api\exception\ExceptionHandler
    'exception_handle' => '',
    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------
    'log' => [
        // 日志记录方式，内置 file socket 支持扩展
        'type' => 'File',
        // 日志保存目录
        'path' => LOG_PATH,
        // 日志记录级别
        'level' => [],
    ],
    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace' => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],
    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------
    'cache' => [
        // 驱动方式
        'type' => 'File',
        // 缓存保存目录
        'path' => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],
    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------
    'session' => [
        'id' => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix' => 'think',
        // 驱动方式 支持redis memcache memcached
        'type' => '',
        // 是否自动开启 SESSION
        'auto_start' => true,
        //设置过期时间
        'expire' => 86400,
    ],
    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie' => [
        // cookie 名称前缀
        'prefix' => '',
        // cookie 保存时间
        'expire' => 0,
        // cookie 保存路径
        'path' => '/',
        // cookie 有效域名
        'domain' => '',
        //  cookie 启用安全传输
        'secure' => false,
        // httponly设置
        'httponly' => 'true',
        // 是否使用 setcookie
        'setcookie' => true,
    ],
    //分页配置
    'paginate' => [
        'type' => 'bootstrap',
        'var_page' => 'page',
        'list_rows' => 15,
    ],
    //验证码配置
    'captcha' => [
        // 验证码字符集合
        'codeSet' => '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',
        // 验证码字体大小(px)
        'fontSize' => 18,
        // 是否画混淆曲线
        'useCurve' => false,
        //使用中文验证码
        'useZh' => false,
        // 验证码图片高度
        'imageH' => 40,
        // 验证码图片宽度
        'imageW' => 130,
        // 验证码位数
        'length' => 4,
        // 验证成功后是否重置
        'reset' => true
    ],
    // +----------------------------------------------------------------------
    // | Token设置
    // +----------------------------------------------------------------------
    'token' => [
        // 驱动方式
        'type' => 'Mysql',
        // 缓存前缀
        'key' => 'i3d6o32wo8fvs1fvdpwens',
        // 加密方式
        'hashalgo' => 'ripemd160',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],
    //FastAdmin配置
    'fastadmin' => [
        //是否开启前台会员中心
        'usercenter' => true,
        //登录验证码
        'login_captcha' => false,
        //登录失败超过10次则1天后重试
        'login_failure_retry' => true,
        //是否同一账号同一时间只能在一个地方登录
        'login_unique' => false,
        //登录页默认背景图
        'login_background' => "/assets/img/loginbg.jpg",
        //是否启用多级菜单导航
        'multiplenav' => true,
        //自动检测更新
        'checkupdate' => false,
        //版本号
        'version' => '1.0.0.20190111_beta',
        //API接口地址
        'api_url' => 'https://api.fastadmin.net',
    ],
    'subdivision' => [
        '1' => '天',
        '2' => '小时',
        '3' => '半小时',
    ],
    //订单消息推送设置
    'news' => [
        '1' => '您有新的订单',
        '2' => '您有未发货订单,请及时处理',
        '3' => '您有待处理退款订单，请及时处理',
        '4' => '您有待发货积分订单，请及时处理',
    ],
    //订单消息推送跳转url
    'url' => [
        '1' => 'http://yanyu.0791jr.com/admin/litestore/litestoreorder?ref=addtabs', //普通商品订单
        '2' => 'http://yanyu.0791jr.com/admin/litestore/litestoreorderrefund?ref=addtabs', //售后订单
        '3' => '', //积分商城订单
    ],
    //用户接收订单信息
    'push' => [
        '1' => '您的订单已发货',
        '2' => '您的售后订单已处理',
        '3' => '您的积分商城订单已发货',
    ],

    /*跳转区域配置*/
    'jump' => [
        ['id' => 0, 'name' => '商品详情'],
        ['id' => 1, 'name' => '秒杀详情'],
        ['id' => 2, 'name' => '拼团详情'],
        ['id' => 3, 'name' => 'VIP专区'],

        ['id' => 4, 'name' => '拼团列表'],
        ['id' => 5, 'name' => '秒杀列表'],
        ['id' => 6, 'name' => '优惠券列表'],
        ['id' => 7, 'name' => '申请代理'],
        ['id' => 8, 'name' => '邀请好友'],

        ['id' => 9, 'name' => 'WEB网页'],
        ['id' => 10, 'name' => '分类'],
    ],

    /**默认好评内容*/

    'DEFAULT_COMMENT' => [
        '0' => '价廉物美，值得购买，好评！产品比想象中的要好很多，和实物没有差别，正好赶上活动就下手了，所以比平时的优惠很多，如果有需要会继续回购的，祝店家生意越来越好！',
        '1' => '好卖家，真有耐心，终于买到想买的东西了。谢谢卖家。',
        '2' => '货到了，比图片上看到的好看多了',
        '3' => '这家店还不错，来买过几次了，服务老客户非常周到，以后会经常来',
    ],


    // | 腾讯云登录设置
    // +----------------------------------------------------------------------
    /*    'tencent_im_config' => [
            'host' => 'https://console.tim.qq.com/', //固定不变

            'sdkappid' => '1400301749',//腾讯云IM控制台创建应用获取

            'key' => '7a3a00865f67f23e61f0bca9abf211cd51bf2c6df4eb15089f77b9cf42d90a7f',//腾讯云IM控制台创建应用获取

            'identifier' => 'yanyu',//管理员名称 腾讯云IM控制台添加
        ],*/
    'distribution' => [
        'agent_type' => '1',
        'consumption' => '20002',
        'commission_type' => '1',
        'min_money' => '300',
    ],

    'wx' => [
        'appid' => 'wx1163fe026e41396c',
        'appsecret' => 'eb5404bec924b217305ac7c9ffaf04a2',
        'mch_id' => '1606609483',
        'key' => 'hGNSHx5VmS7qv7vTCtrpe7Ek76nB5gU3',
    ],

    /*腾讯云直播参数*/
    'TencentSecretId' => 'AKIDA9sLYVjixxiEVWzq8BzYrvlMvLdngUxD',
    'TencentSecretKey' => '92jAKm11lHXaNxINCrHSO4VhfOsJWUBb',

    'templateList' => [
        1 => '8D0YQXjtZYRBwA9OlcAB_Jsh-1RFWH5UUF6mssmULSY',//退款通知
        2 => '8N66Z-N_4R0fLnUkGwEEDtx3zY_H0OYVBK5A6MmHFr8',//提现通知
        3 => 'DWnc0PzWf6iOLMemCnWxfZCWj-ioiZhbZys-piouacs',//活动通知
        4 => 'dJOn07kOBndAI8vR_Bj5cUxidUM8PrLmLlk97C9YxLU',//分销商通知
        5 => 'NTb7l4BuwFKDJVD8btW948RFJV3R2lLnYKjK97-Ysbc',//订单发货通知
        6 => 'cMuPheltYOEl0QT3kDavNoHAHEWMBJ36DA_YNoH22PU',//拼团成功
        7 => 'hGtBocs934ieXDkYQDsQxuMnn17cKm3YdjVxT4u7D7w',//新品上架
    ],

];
