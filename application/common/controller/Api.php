<?php

namespace app\common\controller;

use app\common\library\Auth;
use think\Config;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Hook;
use think\Lang;
use think\Loader;
use think\Request;
use think\Response;
use addons\jpush\library\jpush\Client;
/**
 * API控制器基类
 */
class Api
{

    /**
     * @var Request Request 实例
     */
    protected $request;

    /**
     * @var bool 验证失败是否抛出异常
     */
    protected $failException = false;

    /**
     * @var bool 是否批量验证
     */
    protected $batchValidate = false;

    /**
     * @var array 前置操作方法列表
     */
    protected $beforeActionList = [];

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 权限Auth
     * @var Auth
     */
    protected $auth = null;

    /**
     * 默认响应输出类型,支持json/xml
     * @var string
     */
    protected $responseType = 'json';

    /**
     * 构造方法
     * @access public
     * @param Request $request Request 对象
     */
    public function __construct(Request $request = null)
    {
        $this->request = is_null($request) ? Request::instance() : $request;

        // 控制器初始化
        $this->_initialize();

        // 前置操作方法
        if ($this->beforeActionList) {
            foreach ($this->beforeActionList as $method => $options) {
                is_numeric($method) ?
                    $this->beforeAction($options) :
                    $this->beforeAction($method, $options);
            }
        }
    }

    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        //移除HTML标签
        $this->request->filter('strip_tags');

        $this->auth = Auth::instance();

        $modulename = $this->request->module();
        $controllername = strtolower($this->request->controller());
        $actionname = strtolower($this->request->action());

        // token
        $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', \think\Cookie::get('token')));

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;
        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //初始化
            $this->auth->init($token);
            //检测是否登录
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'), null, 401);
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->auth->check($path)) {
                    $this->error(__('You have no permission'), null, 403);
                }
            }
        } else {
            // 如果有传递token才验证是否登录状态
            if ($token) {
                $this->auth->init($token);
            }
        }

        $upload = \app\common\model\Config::upload();

        // 上传信息配置后
        Hook::listen("upload_config_init", $upload);

        Config::set('upload', array_merge(Config::get('upload'), $upload));

        // 加载当前控制器语言包
        $this->loadlang($controllername);
    }

    /**
     * 加载语言文件
     * @param string $name
     */
    protected function loadlang($name)
    {
        Lang::load(APP_PATH . $this->request->module() . '/lang/' . $this->request->langset() . '/' . str_replace('.', '/', $name) . '.php');
    }


    /**
     * 操作成功返回的数据
     * @param string $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为1
     * @param string $type 输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function success($msg = '', $data = null, $code = 1, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 操作失败返回的数据
     * @param string $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为0
     * @param string $type 输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function error($msg = '', $data = null, $code = 0, $type = null, array $header = [])
    {
        $code = $code == 1 ? 1: 0;
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为0
     * @param string $type 输出类型，支持json/xml/jsonp
     * @param array $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function result($msg, $data = null, $code = 0, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'time' => Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];

        // 如果未设置类型则自动判断
        $type = $type ? $type : ($this->request->param(config('var_jsonp_handler')) ? 'jsonp' : $this->responseType);

        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }
        $response = Response::create($result, $type, $code)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 前置操作
     * @access protected
     * @param string $method 前置操作方法名
     * @param array $options 调用参数 ['only'=>[...]] 或者 ['except'=>[...]]
     * @return void
     */
    protected function beforeAction($method, $options = [])
    {
        if (isset($options['only'])) {
            if (is_string($options['only'])) {
                $options['only'] = explode(',', $options['only']);
            }

            if (!in_array($this->request->action(), $options['only'])) {
                return;
            }
        } elseif (isset($options['except'])) {
            if (is_string($options['except'])) {
                $options['except'] = explode(',', $options['except']);
            }

            if (in_array($this->request->action(), $options['except'])) {
                return;
            }
        }

        call_user_func([$this, $method]);
    }

    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param bool $fail 是否抛出异常
     * @return $this
     */
    protected function validateFailException($fail = true)
    {
        $this->failException = $fail;

        return $this;
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @param mixed $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = Loader::validate();
            $v->rule($validate);
        } else {
            // 支持场景
            if (strpos($validate, '.')) {
                list($validate, $scene) = explode('.', $validate);
            }

            $v = Loader::validate($validate);

            !empty($scene) && $v->scene($scene);
        }

        // 批量验证
        if ($batch || $this->batchValidate)
            $v->batch(true);
        // 设置错误信息
        if (is_array($message))
            $v->message($message);
        // 使用回调验证
        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new ValidateException($v->getError());
            }

            return $v->getError();
        }

        return true;
    }


    //RC4 加密 解密
    public function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;
        $key = md5($key ? $key : UC_KEY);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    public function getPhoneVilifyCode($num)
    {
        $numstr = '';
        // echo $num;
        for ($i = 0; $i < $num; $i++) {
            $numstr = $numstr . rand(0, 9);
        }


        return $numstr;
    }


    /**
     * 解析多张图片
     * */

    public function setPlitJointImages($img , $goods_id = '' ,$video=false)
    {
        if (empty($img))
            return [];

        $imgs = explode(',',$img);
        if ($imgs){
            foreach ($imgs as $k => $v){
                if($video && $k==0 && $v){
                    $imgs[$k] = config('item_url').$v."?vframe/jpg/offset/1";
                }else
                  $imgs[$k] = $v ? config('item_url').$v : "";
            }
        }else
            return [];
        return $imgs;
    }

    /**
     * 拼接数组图片
     * */

   public function joinArrayImages($array_image,$field){
        if($array_image !=null){
            foreach ($array_image as $key=>$value){
                $array_image[$key]['image'] = $value[$field] ? config('item_url').$value[$field] :'';
            }
        }
        return $array_image;
    }

    /*
     * json解析多张图片
     * */

    public function setJsonImages($img)
    {
        $imgs = json_decode($img,true);
        if ($imgs){
            foreach ($imgs as $k => $v){
                $imgs[$k] = $v?config('item_url').$v:"";
            }
        }else
            return [];
        return $imgs;
    }

    /**
     * 获取逗号连接的图片
     * @param $img
     * @return array|string
     */
    public function setCommaImages($img)
    {
        if ($img) {
            $imgs = explode(',', $img);
            if ($imgs) {
                foreach ($imgs as $k => $v) {
                    $imgs[$k] = $v ? config('item_url') . $v : "";
                }
            }
        }else
            return '';
        return $imgs;
    }
    /*
     * 解析二维数组中的多张图片(前提是图片字段为images)
     */
    public function setArrayImages($list,$field)
    {
        if ($list){
            foreach ($list as $k => $v){
                $imgs = explode(',',$v[$field]);
                if ($imgs){
                    foreach ($imgs as $k1 => $v1){
                        $imgs[$k1] = $v1?config('item_url').$v1:"";
                    }
                }
                $list[$k]['images'] = $imgs;
            }
        }else
            return[];
        return $list;
    }

    /**
     * 获取二维数组中单张图片
     * @param $list
     * @param $field
     * @return array
     */
    public function setArrayImage($list,$field)
    {
        if ($list){
            foreach ($list as $k => $v){
                $list[$k]['image'] = empty($v[$field]) ? "" : config('item_url') . $v[$field];
            }
        }
        return $list;
    }





    public function send($ch, $data)
    {
        curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/single_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($ch);
    }

    /**
     * 获取一个随机的两位小数
     * @return float
     */
    function _randomFloat($min ='' , $max= '')
    {
        return round(mt_rand($min, $max) / 10, 2);
    }



    /**
     * @param $alert 推送内容
     * @param $tag 设备标签 就是用户电话
     * @param $value 自定义标准  1)订单已发货  2)售后订单已审核  3）积分商城订单已发货
     * @param $order_id 订单id
     * */
    public function push($alert , $tag ,$value='value' ,$order_id=''){

        $platform = array('ios', 'android');

        $config = get_addon_config('jpush');
        $this->appKey = $config['AppKey'];
        $this->masterSecret = $config['MasterSecret'];
        $this->client = new Client($this->appKey, $this->masterSecret);

        $android_push_array = ['extras'=>['key' => $value,'order_id'=>$order_id]];
        $ios_push_array = ['extras'=>['key' => $value,'order_id'=>$order_id] ,
                           'badge'=>'+1' ,'sound'=>''];

        $push = $this->client->push();
        //$push->setNotificationAlert($alert);
        $push->setPlatform('all'); //设备android ios
        $push->addAlias($tag);//别名
        $push->iosNotification($alert , $ios_push_array); //ios通知消息 自定义字段
        $push->androidNotification($alert,$android_push_array);//android 通知消息 自定义字段
        //$push->addTag($tag); //标签
        $push->setMessage($alert ,'谚语','type');
        $push->setOptions(1000, null, null, false); //第三个参数表示是否是ios生产环境  true 生产环境  false 开发环境
        $result= $push->send();
        return $result['http_code'] == 200 ? $result['body']['msg_id'] : false;

    }

    /*
     * 获取口令信息
     */
    public function getShareInfo($goods_name , $cmmand)
    {
        $cmmand = "【" . $goods_name . "】" . ' ，' . "复制这段描述" . '¥' . $cmmand . '¥' . "到谚语APP";

        return $cmmand;
    }

    /*
     * 获取随机两位小数
     */
    function randomFloat($min = 0, $max = 1) {
        $num =  $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return sprintf("%.2f",$num);
    }

    /**
     * 记入商品有效访问人数
     */
    public function visit($uid ,$goods_id)
    {
        //记入访问次数 （先查询今日是否记入过）
        $day_time = strtotime(date('Y-m-d'));
        $this->visit = model('Visit');
        $data_id = $this->visit->where(['create_time' => ['gt', $day_time], 'status' => 1, 'goods_id'=>$goods_id,'user_id' =>$uid])->value('id');

        if ($data_id) { //修改最后一次访客时间
            $this->visit->save(['create_time' => time()], ['id' => $data_id, 'user_id' => $uid]);
        } else {
            //添加一条记录
            $visit_data = ['status' => 1, 'visit' => 1, 'user_id' => $uid , 'goods_id'=>$goods_id,'create_time' => time()];
            $this->visit->create($visit_data);
        }
        return true;
    }
}
