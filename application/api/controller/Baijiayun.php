<?php

namespace app\api\controller;

use app\common\controller\Api;
//require_once '/usr/local/xunsearch/sdk/php/lib/XS.php';

/**
 * 首页接口
 */
class Baijiayun extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();

    }


    /**
     * 生成签名参数
     *
     * @param array $params 请求的参数
     * @param string $partner_key
     * @return string 生成的签名
     */
    function getSign($params, $partner_key) {
        ksort($params);//将参数按key进行排序
        $str = '';
        foreach ($params as $k => $val) {
            $str .= "{$k}={$val}&"; //拼接成 key1=value1&key2=value2&...&keyN=valueN& 的形式
        }
        $str .= "partner_key=" . $partner_key; //结尾再拼上 partner_key=$partner_key
        dump($str); exit;
        //$str = "end_time=1575688552&partner_id=54134441&start_time=1575681352timestamp=1575681352&title=测试教室2&type=2&partner_key=bLEcfxS+I2GdAvUghF74Ewlxz0QXE1daTMCHDCzhXOy8lya0bs0qouSnWAUXlAW3POQXC4a7qGH4Pm9dhk3TU6p2RdywnUlkHSgGoXNkuQgYtJBeXGXNgxUJkLKyLluH";
        $sign = md5($str); //计算md5值
        return $sign;
    }

    public function demo(){
        $sign = $this->test();echo $sign;exit;
        $url = 'https://b54134441.at.baijiayun.com/openapi/room/create';
        $arr = [
            'partner_id' => 54134441,
            'title' => '测试2',
            'start_time' => 1575704134,
            'end_time' => 1575704134 + 2*3600,
            'timestamp' => 1575704134,
        ];
        $data = '';
        ksort($arr);
        foreach ($arr as $k => $val) {
            $data .= "{$k}={$val}&"; //拼接成 key1=value1&key2=value2&...&keyN=valueN& 的形式
        }
        $data= $data.'sign='.$sign;


        $result = $this->curl_post_raw($url ,$data);
        $this->success('',$result);
    }

    public function test()
    {
        $params = [
            "end_time" => 1575704134+ 3600*2,
            "start_time" => 1575704134,
            "timestamp" => 1575704134,
            "partner_id" => 54134441,
            "title" => "测试教室2",
            "type" => 2,
        ];

        $partner_key = 'bLEcfxS+I2GdAvUghF74Ewlxz0QXE1daTMCHDCzhXOy8lya0bs0qouSnWAUXlAW3POQXC4a7qGH4Pm9dhk3TU6p2RdywnUlkHSgGoXNkuQgYtJBeXGXNgxUJkLKyLluH';
        return $this->getSign($params, $partner_key);
    }



    /*
 * 百家云请求API集成
 * $api API请求接口 例如 拉取资料  v4/profile/portrait_get
 * $data post参数 根据api要求传参数 ，数组
 */
    function curl_post_raw($url, $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}