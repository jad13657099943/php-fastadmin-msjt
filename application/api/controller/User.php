<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Hook;
use think\Validate;
use \app\common\library\Token;
use \fast\Http;


/**
 * 会员接口
 */
class User extends Api
{


    protected $noNeedLogin = ['wxlogin', 'login', 'forget_pwd', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third', 'send_code', 'addCmmand', 'analysisCommand', 'test', 'auth_bind_mobile', 'auth_login'];
    protected $token = '';
    protected $noNeedRight = '*';
    protected $_user = NULL;
    //Token默认有效时长
    protected $keeptime = 0;

    public function _initialize()
    {
        $this->token = $this->request->post('token');
        if ($this->request->action() == 'login' && $this->token) {
            $this->request->post(['token' => '']);
        }
        parent::_initialize();
        $this->sms_code = model('Sms');
        $this->user = model('User');
        //$this->auth->id;
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }


    /**
     * 微信授权登录
     */
    public function wxlogin()
    {
        $config = config('wx');
        $code = $this->request->post("code");
        $invitation_code = $this->request->post("invitation_code");
        !$code && $this->error("code不正确");
        $params = [
            'appid' => $config['appid'],
            'secret' => $config['appsecret'],
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];

        $result = Http::get("https://api.weixin.qq.com/sns/jscode2session", $params);
        if ($result) {
            $json = json_decode($result, true);
            if (isset($json['openid'])) {
                $info = $this->user->find_data(['wxopenid' => $json['openid']]);
                if ($info) {
                    $this->auth->direct($info['id']);
                    $user = $this->auth->getUser();
                    $user->session_key = $json['session_key'];
                    $user->save();
                    $this->success('', ['openid' => $json['openid'], 'token' => $this->auth->getToken()]);

                } else {
                    $user_info['nickname'] = input('nickname');
                    $user_info['headimgurl'] = input('headimgurl');
                    $user_info['sex'] = !input('sex') ? '' : input('sex');
                    $user_info['openid'] = $json['openid'];
                    $user_info['invitation_code'] = $invitation_code;
                    $user_info['session_key'] = $json['session_key'];
                    $this->auth->wxRegister((array)$user_info);
                    $this->success('登录成功', $this->auth->getUserInfo());
                }
            } else {
                $this->error("登录失败");
            }
        }
    }

    public function updateUserInfo()
    {
        $nickname = $this->request->param('nickname');
        $avatar = $this->request->param('avatar');

        !$nickname && $this->error('nickname不能为空');
        !$avatar && $this->error('avatar不能为空');

        $user = $this->auth->getUser();
        $user->avatar = $avatar;
        $user->nickname = $nickname;
        $user->save();
        $this->success('更新成功');
    }

    public function decryptUserInfo()
    {
        $data = $this->request->param('encryptedData');
        $iv = $this->request->param('iv');
        !$data && $this->error('data不能为空');
        !$iv && $this->error('iv不能为空');
        $data = openssl_decrypt(base64_decode($data), 'AES-128-CBC', base64_decode($this->auth->session_key), 1, base64_decode($iv));
        $result = json_decode($data,true);
        if($result){
            $this->user->save(['mobile' => $result['phoneNumber']], ['id' => $this->auth->id]) && $this->success('绑定成功');
            $this->success('获取成功', $result['phoneNumber']);
        }
        $this->error('获取失败');
    }


    /**
     * 绑定手机号
     * @param string mobile  手机号
     * @param string code 验证码
     */
    public function bindMobile()
    {
        $mobile = $this->request->param('mobile');
        $code = $this->request->param('code');

        !$mobile && $this->error('mobile不能为空');
        !$code && $this->error('code不能为空');

        if (Sms::check($mobile, $code, 'bindmobile')) {
            Hook::listen('user_bind_mobile_success', $this->auth->getUser());
            $this->user->save(['mobile' => $mobile], ['id' => $this->auth->id]) && $this->success('绑定成功');
        } else {
            $this->error('验证码错误');
        }
        $this->error('未知原因，绑定失败');

    }

    /**
     * 会员登录
     *
     * @param string $account 账号
     * @param string $password 密码
     * ENCODE  加密
     * DECODE  解密
     */
    public function login()
    {
        // $ret = base64_encode(serialize('wwww'));//加密 bin2hex($re)
        // $res = base64_decode($ret); dump($res); exit;//解密
        //$this->success(__('Logged in successful'), unserialize($res));

        $mobile = $this->request->request('mobile');
        $password = $this->request->request('password');
        if (!$mobile || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($mobile, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success('登录成功', $data);//$this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /*
     * 手机验证码登录
     *
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('code');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 1)) {
            $this->error(__('Captcha is incorrect'));
            $this->error('验证码错误');
        }
        $user = \app\common\model\User::getByMobile($mobile);

        if ($user) {
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register('', $mobile, []);
        }
        if ($ret) {
            // Sms::flush($mobile, 1);
            $data = ['userinfo' => $this->auth->getUserinfo()];
//            $this->success(__('Logged in successful'), $data);
            $this->success('登录成功', $data);
        } else {
            $this->error($this->auth->getError());
        }
    }


    /*
     * 注册会员
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $code 验证码
     * @param string $mobile 手机号
     */
    public function register()
    {
        $password = $this->request->request('password');
        $confirm_password = $this->request->request('confirm_password');
        $mobile = $this->request->request('mobile');
        $code = $this->request->request('code');
        $invite_code = $this->request->request('invite_code');
        if (!$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($password !== $confirm_password) {
            $this->error('两次输入密码不一致');
        }
        if (strlen($password) < 6 || strlen($password) > 16) {
            $this->error('密码长度应为6-16位之间');
        }

        /*if (!Sms::check($mobile, $code, 2)) {
            $this->error(__('Captcha is incorrect'));
        }*/
        /*if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }*/
        if (empty($this->user->getField(['invitation_code' => $invite_code], 'id'))) {
            $this->error('邀请码不存在');
        }
        $ret = $this->auth->register($password, $mobile, $invite_code, []);
        if ($ret) {   //Sms::flush($mobile, 2);
            $data = ['userinfo' => $this->auth->getUserinfo()];
//            $this->success(__('Sign up successful'), $data);
            $this->success('注册成功', $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /*
     * 忘记密码
     *
     *
     * @param string $newpassword 密码
     * @param string $code 验证码
     * @param string $mobile 手机号
     * @param string $confirm_password 重复密码
     */
    public function forget_pwd()
    {
        $mobile = $this->request->request("mobile");
        $newpassword = $this->request->request("newpassword");
        $captcha = $this->request->request("captcha");
        $confirm_password = $this->request->request("confirm_password");
        if (!$newpassword || !$captcha) {
            $this->error('参数不存在');
        }
        if ($confirm_password != $newpassword) {
            $this->error('两次输入密码不一致');
        }

        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if (!$user) {
            $this->error('用户不存在');
        }
        $ret = Sms::check($mobile, $captcha, 3);
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }

        $salt = Random::alnum();
        $newpassword = $this->auth->getEncryptPassword($newpassword, $salt);
        $data = ['password' => $newpassword, 'salt' => $salt];
        if ($this->user->edit_data(['mobile' => $mobile], $data) != false) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    /*
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
//        $this->success(__('Logout successful'));
        $this->success('注销成功');
    }

    /*
     * 修改会员个人信息
     *
     * @param string $avatar 头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio 个人简介
     * @param string $mobile 手机号
     * @param string $gender 性别
     * @param  $birthday 生日
     *
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->request('username');
        $nickname = $this->request->request('nickname');
        $avatar = $this->request->request('avatar');
        $birthday = $this->request->request('birthday');
        $gender = $this->request->request('gender');
        $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
        if ($exists) {
//            $this->error(__('Username already exists'));
            $this->error('用户名已存在');
        }

        !empty($birthday) && $user->birthday = $birthday;
        !empty($username) && $user->username = $username;
        !empty($avatar) && $user->avatar = $avatar;
        !empty($gender) && $user->gender = $gender;
        if ($user->save()) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }

    }

    /*
     * 修改邮箱
     *
     * @param string $email 邮箱
     * @param string $captcha 验证码
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->request('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /*
     * 修改手机号
     *
     * @param string $email 手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @param string $platform 平台名称
     * @param string $code Code码
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->request("platform");
        $code = $this->request->request("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过cdoe换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo' => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
//                $this->success(__('Logged in successful'), $data);
                $this->success('登录成功', $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @param string $mobile 手机号
     * @param string $newpassword 新密码
     * @param string $captcha 验证码
     */
    public function resetpwd()
    {
        $type = $this->request->request("type");
        $mobile = $this->request->request("mobile");
        $email = $this->request->request("email");
        $newpassword = $this->request->request("newpassword");
        $captcha = $this->request->request("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 发送验证码
     * @param mobile 手机号 type 1登录 2 注册 3忘记密码 4忘记支付密码 5第三方登录绑定
     */

    public function send_code()
    {
        $mobile = $this->request->request('mobile');
        $type = $this->request->request('type');
        //检测手机号是否注册
        $register_search = $this->user->getUserInfo(['mobile' => $mobile], 'id,mobile');

        switch ($type) {
            case 2:
                $register_search && $this->error('用户已注册');
                break;
            case 3:
                !$register_search && $this->error('手机号未注册');
                break;
            case 4:
                $mobile != $this->auth->mobile && $this->error('请输入账户绑定手机号');
                break;
        }
        header("Content-Type:text/html;charset=utf-8");
        $apikey = "c6f9a59ef308f13b7db8ebd64107ba12"; //修改为您的apikey(https://www.yunpian.com)登录官网后获取
        $mobile = $mobile; //请用自己的手机号代替
        (!check_mobile($mobile)) && $this->error("手机号格式错误");
        $code = $this->getPhoneVilifyCode(4);

        $text = '【彦语APP】您的验证码是' . $code . '。如非本人操作，请忽略本短信';
        $ch = curl_init();
        /* 设置验证方式 */
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));
        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        /* 设置超时时间*/
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 发送短信
        $data = array('text' => $text, 'apikey' => $apikey, 'mobile' => $mobile);
        $json_data = $this->send($ch, $data);
        $array = json_decode($json_data, true);
        curl_close($ch);
        if ($array['code'] == 0) {
            $sms_data['createtime'] = time();
            $sms_data['code'] = $code;
            $sms_data['mobile'] = $mobile;
            $sms_data['type'] = $type;
            $sms_data['event'] = $text;
            if ($this->sms_code->insert($sms_data))
                //删除sms表里已有的用户信息
//                Sms::flush($mobile , $type);
                $this->success("发送成功");

        } else {
            $this->error('发送失败' . $array['msg']);
        }
    }

    /**
     * 授权登录
     * @param openid 第三方登录openid
     * @param type 1 qq 2 微信
     */
    public function auth_login()
    {
        $params = $this->request->request();
        !$params['openid'] && $this->error('openid不存在');
        !$params['type'] && $this->error('openid不存在');

        switch ($params['type']) {
            case 1:
                $search = ['qqopenid' => $params['openid']];
                break;
            case 2:
                $search = ['wxopenid' => $params['openid']];
                break;
            default:
                $this->error('type参数错误');
                break;
        }
        $user_info = $this->user->getUserInfo($search);

        if ($user_info) {
            $token = Random::uuid();
            Token::set($token, $user_info['id'], $this->keeptime);
            if ($token) {
                $return_data = [
                    'token' => $token,
                    'is_band' => 1,
                    'avatar' => config('item_url') . $user_info['avatar'],
                    'username' => $user_info['username'],
                    'mobile' => $user_info['mobile'],
                ];
                $this->success('登录成功', $return_data);
            } else {
                $this->error('登录失败');
            }
        } else {
            $this->success("未绑定手机号", ['is_band' => 0]);
        }
    }

    /**
     * 第三方登录绑定手机号码
     * @param nickname 昵称
     * @param mobile 手机号
     * @param code 验证码
     * @param head 用户头像
     * @param sex 1男 2女
     * @param type 1 qq 2微信
     * @param openid 第三方openid
     */
    public function auth_bind_mobile()
    {

        $params = $this->request->request();
        if (!$params['mobile']) {
            $this->error('手机号不能为空');
        }
        if (!$params['code']) {
            $this->error('code不能为空');
        }
        if (!$params['type']) {
            $this->error('type不能为空');
        }
        if (!$params['openid']) {
            $this->error('openid不能为空');
        }

        if (!Sms::check($params['mobile'], $params['code'], 5)) {
            $this->error(__('Captcha is incorrect'));
        }

        $user_info = $this->user->getUserInfo(['mobile' => $params['mobile']]);
        if ($user_info) {
            switch ($params['type']) {
                case 1:
                    $update_data = ['qqopenid' => $params['openid']];
                    $user_info['wxopenid'] && $this->error('该手机号已被绑定');
                    break;
                case 2:
                    $update_data = ['wxopenid' => $params['openid']];
                    $user_info['qqopenid'] && $this->error('该手机号已被绑定');
                    break;
                default:
                    $this->errot('type参数错误');
                    break;
            }

            $res = $this->user->updata_data(['id' => $user_info['id']], $update_data);
            if ($res === false) {
                $this->error("绑定失败");
            } else {
                $token = Random::uuid();
                Token::set($token, $user_info['id'], $this->keeptime);
                $return_data = [
                    'token' => $token,
                    'username' => $user_info['username'],
                    'avatar' => $user_info['avatar'],
                    'mobile' => $user_info['mobile'],
                ];
                //清空验证
                Sms::flush($params['mobile'], 5);
                $this->success("绑定成功", $return_data);
            }
        } else {
            //手机号码未注册;
            $user = [];
            $ip = request()->ip();
            $user['username'] = $params['username'];
            $user['mobile'] = $params['mobile'];
            $user['avatar'] = $params['avatar'];
            $user['createtime'] = time();
            $user['jointime'] = time();
            $user['joinip'] = $ip;
            $user['status'] = 'normal';
            $user['balance'] = 0;//注册新用户余额为0
            if ($params['type'] == 1) {
                $user['qqopenid'] = $params['openid'];
            } else {
                $user['wxopenid'] = $params['openid'];
            }

            $this->user->startTrans();
            try {

                $this->user->save($user);
                $user_id = $this->user->getLastInsID();
                if (!$user_id) {
                    $this->user->rollback();
                    $this->error("绑定失败");
                }
                $token = Random::uuid();
                Token::set($token, $user_id, $this->keeptime);

                if (!$token) {
                    $this->user->rollback();
                    $this->error("登录失败");
                }
                $this->user->commit();
                $user_info = [
                    'token' => $token,
                    'username' => $params['username'],
                    'avatar' => $params['avatar'],
                    'mobile' => $params['mobile'],
                ];
                //清空验证
                Sms::flush($params['mobile'], 5);
                $this->success('绑定成功', $user_info);
            } catch (Exception $exception) {
                $this->user->rollback();
                $this->error("绑定失败" . $exception->getMessage());
            }
        }

    }
}