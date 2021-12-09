<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\UserRebate;
use think\Db;
/**
 * 快递鸟查询控制器
 */
class Express extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * Json方式 查询订单物流轨迹
     * @param $ShipperCode 物流公司简称
     * @param $LogisticCode 订单编号
     * @param $delivery_company 物流公司名称
     * @param $img 货物图片
     */
    function getOrderTracesByJson($ShipperCode ,$LogisticCode , $delivery_company ,$img,$mobile)
    {
        $requestData = "{'OrderCode':'','ShipperCode':'".$ShipperCode."','LogisticCode':'".$LogisticCode."','CustomerName' :'".$mobile."'}";
//        dump($requestData);die;
        $Config = get_addon_config('kdniao');
        $datas = array(
            'EBusinessID' => $Config['EBusinessID'],
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $Config['AppKey']);
        $result = $this->sendPost($Config['ReqURL'], $datas);
        //根据公司业务处理返回的信息......
        $result = json_decode($result, true);
//        dump($result);die;
        //2-在途中,3-签收,4-问题件
        switch ($result['State']) {
            case 0:
                $result['State'] = '暂无轨迹信息';
                break;
            case 2:
                $result['State'] = '在途中';
                break;
            case 3:
                $result['State'] = '已签收';
                break;
            case 4:
                $result['State'] = '问题件';
                break;
        }

        if($result['StateEx']) {
            switch ($result['StateEx']) {
                case 201:
                    $result['State'] = '到达派件城市';
                    break;
                case 202:
                    $result['State'] = '派件中';
                    break;
                case 211:
                    $result['State'] = '已放入快递柜或驿站';
                    break;
                case 302:
                    $result['State'] = '派件异常后最终签收';
                    break;
                case 301:
                    $result['State'] = '正常签收';
                    break;
                case 304:
                    $result['State'] = '代收签收';
                    break;
                case 311:
                    $result['State'] = '快递柜或驿站签收';
                    break;
                case 401:
                    $result['State'] = '发货无信息';
                    break;
                case 402:
                    $result['State'] = '超时未签收';
                    break;
                case 403:
                    $result['State'] = '超时未更新';
                    break;
                case 404:
                    $result['State'] = '拒收(退件)';
                    break;
                case 405:
                    $result['State'] = '派件异常';
                    break;
                case 406:
                    $result['State'] = '退货签收';
                    break;
                case 407:
                    $result['State'] = '退货未签收';
                    break;
                case 412:
                    $result['State'] = '快递柜或驿站超时未取';
                    break;
            }
        }
        $result['name'] = $delivery_company;
        $result['image'] = $img;
        $result['Traces'] = array_reverse($result['Traces']);

        if ($result['Success']) {
            unset($result['EBusinessID'] , $result['OrderCode'] , $result['Reason'], $result['Success'],  $result['ShipperCode']);

            $this->success('',$result);
        } else
            $this->error($result['Reason']);
    }

    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
        public function sendPost($url, $datas) {

        $temps = array();

        foreach ($datas as $key => $value) {

        $temps[] = sprintf('%s=%s', $key, $value);

        }

        $post_data = implode('&', $temps);

        $url_info = parse_url($url);

        if(empty($url_info['port']))

        {

                $url_info['port'] = 80;

    }

    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";

    $httpheader .= "Host:" . $url_info['host'] . "\r\n";

    $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";

    $httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";

    $httpheader .= "Connection:close\r\n\r\n";

    $httpheader .= $post_data;

    $fd = fsockopen($url_info['host'], $url_info['port']);
    fwrite($fd, $httpheader);


    $gets = "";

    $headerFlag = true;

    while (!feof($fd)) {

                if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {

                    break;

    }

    }

    while (!feof($fd)) {

                $gets .= fread($fd, 128);

    }

    fclose($fd);
    return $gets;

    }
    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
    }
}