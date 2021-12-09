<?php


namespace app\api\controller\msjt;


use addons\epay\library\Service;
use app\api\controller\Order;

class Refund
{
    public function refundNotify()
    {
        $data = $this->decode();
        !$data && die;
        if (Order::refundCallBack($data)) {
            $this->wxSuccess();
            exit();
        }
    }


    /**
     * xml转数组
     * @param $xml
     * @return mixed
     */
    protected function fromXml($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * 解析退款回调数据
     * @return bool|mixed
     */
    protected function decode()
    {
        $data = file_get_contents("php://input");
        $data = $this->fromXml($data);
        if ($data['return_code'] == 'SUCCESS') {
            $dataStr = base64_decode($data['req_info']);
            $config = Service::getConfig('wechat')['wechat'];
            $key = md5($config['key']);
            $reqInfo = openssl_decrypt($dataStr, 'AES-256-ECB', $key, OPENSSL_RAW_DATA);
            return $this->fromXml($reqInfo);
        }
        return false;
    }

    /**
     * 通知微信回调成功
     */
    protected function wxSuccess()
    {
        echo '<xml>
  <return_code><![CDATA[SUCCESS]]></return_code>
  <return_msg><![CDATA[OK]]></return_msg>
</xml>';
    }
}