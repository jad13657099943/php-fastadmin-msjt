<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use think\Tencentapi;
use \fast\Http;
use think\cache\driver\Redis;

require_once VENDOR_PATH . 'tencentcloud-sdk-php/TCloudAutoLoader.php';

use TencentCloud\Live\V20180801\LiveClient;
// 导入要请求接口对应的Request类
use TencentCloud\Live\V20180801\Models\DescribeLiveDomainRequest; //API接口类
use TencentCloud\Common\Credential;

//require_once '/usr/local/xunsearch/sdk/php/lib/XS.php';


/**
 * 首页接口
 */
class Im extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();

        /*  try {

              // 实例化一个证书对象，入参需要传入腾讯云账户secretId，secretKey
              $cred = new Credential(config('TencentSecretId'), config('TencentSecretKey'));

              $client = new LiveClient($cred, "ap-shanghai");//不用动

              // 实例化一个请求对象
              $req = new DescribeLiveDomainRequest(); //接口类
              $req->DomainName = 'aliyun.0791jr.com'; //下面是对应参数
              $req->Action = 'DescribeLiveDomain';
              $req->Version = '2018-08-01';


              // 通过client对象调用想要访问的接口，需要传入请求对象
              $resp = $client->DescribeLiveDomain($req);

              print_r($resp->toJsonString());exit;
          }
          catch(TencentCloudSDKException $e) {
              echo $e;
          }*/


        $this->config = config('tencent_im_config');
        $this->api = new Tencentapi($this->config['sdkappid'], $this->config['key']);
    }


    /**
     * 微信授权登录
     */
    public function wxlogin()
    {

        $url = 'https://api.weixin.qq.com/wxaapi/broadcast/room/create?access_token=' . getAccessToken();
        $params = [
            'name'=> "测试直播房间1",  // 房间名字
              'coverImg'=> "",   // 通过 uploadfile 上传，填写 mediaID
              'startTime'=>'',   // 开始时间
              'endTime'=>'' , // 结束时间
              'anchorName'=> "zefzhang1",  // 主播昵称
              'anchorWechat'=> "WxgQiao_04",  // 主播微信号
              'shareImg'=> "" ,  //通过 uploadfile 上传，填写 mediaID
              'type'=> 1 , // 直播类型，1 推流 0 手机直播
              'screenType'=> 0,  // 1：横屏 0：竖屏
        ];
        Http::post($url, json_encode($params));

        $redis = new Redis();
        dump($redis->get("user_info607"));exit();


        //1）2种配送方式  2）配送方式  3）自提
        $config = config('site.delivery_methods');
        $delivery_methods = $config[0] === "delivery" && $config[1] === "self_mention" ? 1 : ($config[0] === "delivery" ? 2 : 3);

        $config = config('site.goods_delivery_methods');
        $delivery_methods = $config[0] === "goods_delivery" && $config[1] === "goods_self_mention" ? 1 : ($config[0] === "goods_delivery" ? 2 : 3);
        dump($value); exit();

        $code = $this->request->get("code");
      //  $invitation_code = $this->request->post("invitation_code");
        !$code && $this->error("code不正确");
        $params = [
            'appid' => "wx45b034b3d4acf832",
            'secret' => "69c4fc1395ce2a8bbefc6d4ae2ea52c6",
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];
       // dump($params); exit();
        $result = Http::get("https://api.weixin.qq.com/sns/jscode2session", $params);
        $this->user = db('member');
        if ($result) {
            $json = json_decode($result, true);
            dump($json);
            exit();
            if (isset($json['openid'])) {
                $info = $this->user->where(['wxopenid' => $json['openid']])->find();
                if ($info) {
                    $this->auth->direct($info['id']);
                    $this->success('', ['openid' => $json['openid'], 'token' => $this->auth->getToken()]);
                    die;
                    // $this->success('登录成功', $this->auth->getUserInfo());
                } else {
                    $user_info['nickname'] = input('nickname');
                    $user_info['headimgurl'] = input('headimgurl');
                    //  $user_info['sex'] = !input('sex') ? '' : input('sex');
                    $user_info['wxopenid'] = $json['openid'];
                    // $user_info['invitation_code'] = $invitation_code;
                    $this->user->insert($user_info);
                    $this->success('登录成功', ['token' => $this->auth->getToken()]);
                }
            } else {
                $this->error("登录失败");
            }
        }
    }

    public function test2()
    {
        $result = db('user_rebate')->field('uid,first_id')->select();

        foreach ($result as $k => $value) {
            $pid = db('user')->where('id', $value['uid'])->value('pid');
            if ($pid == 0) {
                db('user')->where('id', $value['uid'])->update(['pid' => $value['first_id']]);
            }
        }
        dump($result);
        exit();
    }

    /**
     * 不是接口
     * 限制一个用户购买一个商品 限制N天内购买N次
     * site.limit_day
     * site.limit_number
     * @param $goods_id
     * @return array
     */
    public function limit_goods($goods_id, $uid)
    {
        $limituser_mode = model('Limituser');
        if ($limituser_mode->check_count(['goods_id' => $goods_id, 'uid' => $uid])) {
            $limit_day = Config('site.limit_day');
            $limit_number = Config('site.limit_number');

            //查询最近购买次数 购买时间
            $limit_time = strtotime('-' . $limit_day . 'day');
            $where = ['createtime' => ['egt', $limit_time], 'goods_id' => $goods_id, 'user_id' => $uid];

            $order_goods_model = model('Litestoreordergoods');
            $order_ids = $order_goods_model->column('order_id');

            if ($order_ids != null) {
                $order = model('Litestoreorder'); //查询规定时间能购买次数 'pay_status' => 20,

                $where['id'] = ['in', $order_ids];
                unset($where['goods_id']);
                $order_count = $order->where($where)->count();

                if ($order_count >= $limit_number) {
                    $this->error('购买数量受限,不能购买');
                }
            }
        }
        return true;
    }


    /*
     * 腾讯聊天请求API集成
     * $api API请求接口 例如 拉取资料  v4/profile/portrait_get
     * $data post参数 根据api要求传参数 ，数组
     */
    function curl_post_raw($data = [])
    {   //获取签名
        // $usersig = $this->api->genSig($this->config['identifier']);

        //腾讯云请求路径拼接
        // $url = $this->config['host'].$api.'?sdkappid=' . $this->config['sdkappid'] . '&identifier=' .
        // $this->config['identifier'] . '&usersig=' . $usersig . '&random=99999999&contenttype=json';

        $url = 'https://iclass.api.qcloud.com/paas/v1/user/register?';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 腾讯云互动课堂
     * @param
     * @return void
     * User: jrmac
     * Date: 2020/1/9
     * Time: 5:13 下午
     */
    public function Tencent()
    {

        $usersig = $this->api->genSig('jiaruikeji');
        dump($usersig);
        exit;
        $tic_key = 'JffRPESguq0mabDiyiZTb0iBiykeS2we';
        $expire_time = time() + 120;
        //$data['list'] = model('User')->limit('1')->field('id user_id ,password')->select()->toArray();dump($data); exit();
        $data['list'] =
            [
                [
                    'user_id' => '1111222',
                    'password' => 'dshfwe1222',
                    'role' => 'student',
                ]
            ];

        $return = $this->curl_post_raw($data);
        dump($return);
        exit();
    }


    public function demo()
    {
        $list = Db::view('Other', '*')->where('id', '791')->select();
        dump($list);
        exit();
    }


    public function add()
    {
        $params = $this->request->request('');
        unset($params['s']);
        $params['create_time'] = date('Y-m-d h:i', time());
        $add = Db::table('jrkj_demo')->insert($params);
        $this->success('获取成功', $add);
    }

    public function search()
    {
        $search = $this->request->request('search');
        $where['name|idnumber'] = ['like', '%' . $search . '%'];
        $add = Db::table('jrkj_demo')->where($where)->select();
        $this->success('获取成功', $add);
    }

    public function detail()
    {
        $id = $this->request->request('id');
        $where['id'] = $id;
        $add = Db::table('jrkj_demo')->where($where)->find();
        $this->success('获取成功', $add);
    }

    public function red_add()
    {
        $params = $this->request->request('');
        unset($params['s']);
        $params['create_time'] = date('m-d h:i', time());
        $add = Db::table('jrkj_red_packet')->insert($params);
        $this->success('获取成功', $add);
    }

    public function red_select()
    {
        $uid = $this->request->request('uid');
        $add = Db::table('jrkj_red_packet')->where(['uid' => $uid])->select();
        $this->success('获取成功', ['list' => $add]);
    }

    public function red_home()
    {
        $list = Db::table('jrkj_red_name')->select();
        foreach ($list as $k => $v) {
            $list[$k]['num'] = Db::table('jrkj_red_packet')->where(['uid' => $v['id']])->sum('num');
            $list[$k]['money'] = Db::table('jrkj_red_packet')->where(['uid' => $v['id']])->sum('money');
        }
        $num = Db::table('jrkj_red_packet')->sum('num');
        $money = Db::table('jrkj_red_packet')->sum('money');

        $this->success('获取成功', ['list' => $list, 'num' => $num, 'money' => $money]);
    }

    public function red_homes()
    {
        $params = $this->request->request('id');
        $this->success('获取成功', ['status' => $status ? 1 : 2, 'type' => 'https://www.baidu.com/']);
    }


    public function xidi_add()
    {
        $params = $this->request->request('');
        unset($params['s']);
        $params['create_time'] = date('m-d h:i', time());
        $add = Db::table('jrkj_xidi')->insert($params);
        $this->success('获取成功', $add);
    }

    public function xidi_edit()
    {
        $params = $this->request->request('');
        unset($params['s']);
        $params['create_time'] = date('m-d h:i', time());
        $add = Db::table('jrkj_xidi')->update($params);
        $this->success('获取成功', $add);
    }

    public function xidi_select()
    {
        $status = $this->request->request('status');
        if ($status) {
            $where = ['status' => $status];
        } else {
            $where = [];
        }
        $add = Db::table('jrkj_xidi')->where($where)->select();
        $this->success('获取成功', ['list' => $add]);
    }

    public function xidi_select2()
    {
        $status = $this->request->request('status');

        if (!$status) {
            $where = ['status' => 1];
        } else {
            $where = ['status' => ['neq', 1]];
        }
        $add = Db::table('jrkj_xidi')->where($where)->select();
        $this->success('获取成功', ['list' => [$add]]);
    }

    public function xidi_order()
    {
        $id = $this->request->request('id');
        $status = $this->request->request('status');
        $params = ['id' => $id, 'status' => $status ? $status : 2];
        $add = Db::table('jrkj_xidi')->update($params);
        $add = $this->xidi_select2();
        $this->success('获取成功', ['list' => [$add]]);
    }


    public function add_qiu()
    {
        $params = $this->request->request('');
        unset($params['s']);
        $params['create_time'] = date('Y-m-d h:i', time());
        $add = Db::table('jrkj_qiu')->insert($params);
        $this->success('获取成功', $add);
    }

    public function select_qiu()
    {
        $status = $this->request->request('');
        $add = Db::table('jrkj_qiu')->select();
        $this->success('获取成功', ['list' => $add]);
    }

    public function find_qiu()
    {
        $id = $this->request->request('id');
        $add = Db::table('jrkj_qiu')->where(['id' => $id])->find();
        $this->success('获取成功', $add);
    }


    public function login()
    {
        $name = $this->request->request('name');
        $version = $this->request->request('version');

        $ver_info = Db::table('jrkj_ver')->where(['name' => $name])->find();

        $ver_info['update'] = $ver_info['version'] > $version ? true : false;
        $ver_info['name'] = $name;

        $this->success('获取成功', $ver_info);

    }
}