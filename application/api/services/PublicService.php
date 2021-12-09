<?php

namespace app\api\services;

use think\Env;

class PublicService extends CommonService
{

    /**
     * wx公众号/access_token授权
     * @param string $code
     * @return mixed
     */
    protected function getAccessToken($code = '', $type = 'gzh')
    {

        $appid = Env::get('appid');
        $secret = Env::get('appsecret');

        if (!$appid || !$secret) {
            $this->error('请在.env配置参数');
        }

        $grant_type = 'authorization_code';

        //公众号
        if ($type == 'gzh') {
            $path = 'https://api.weixin.qq.com/sns/oauth2/access_token';
            $code_text = 'code';
        } else {
            //小程序
            $path = 'https://api.weixin.qq.com/sns/jscode2session';
            $code_text = 'js_code';
        }

        $url = $path . '?appid=' . $appid . '&secret=' . $secret . '&' . $code_text . '=' . $code . '&grant_type=' . $grant_type;
        $info = $this->curlGet($url);
        $info->url = $url;
        if (empty($info->openid)) $this->error('微信授权失败' . json_encode($info));
        return $info;

    }

    /**
     * wx用户信息-公众号
     * @param $token
     * @param $openid
     * @return mixed
     */
    protected function getWxUserInfo($token, $openid)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $token . '&openid=' . $openid . '&lang=zh_CN';
        return $this->curlGet($url);
    }

    /**
     * xml转json
     * @return mixed
     */
    protected function xmlJson()
    {
        $testxml = file_get_contents("php://input");
        $jsonxml = json_encode(simplexml_load_string($testxml, 'SimpleXMLElement', LIBXML_NOCDATA));
        return $result = json_decode($jsonxml);
    }

    /**
     * 验证是否支付成功
     * @param $result
     * @return false
     */
    protected function isSuccess($result)
    {

        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
            return $result;
        } else {
            return false;
        }

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


    /**
     * 获取小程序手机号
     * @param $sessionKey
     * @param $encryptedData
     * @param $iv
     * @param $data
     * @return int|mixed
     * @throws \think\Exception
     */
    protected function decryptData($sessionKey, $encryptedData, $iv)
    {
        $appid = \think\Env::get('appid');
        if (!$appid) {
            $this->error('请在.env配置参数');
        }
        $IllegalAesKey = -41001;
        $IllegalIv = -41002;
        $IllegalBuffer = -41003;

        if (strlen($sessionKey) != 24) {
            return $IllegalAesKey;
        }
        $aesKey = base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return $IllegalIv;
        }
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj = json_decode($result);
        if ($dataObj == NULL) {
            return $IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $appid) {
            return $IllegalBuffer;
        }
        //phoneNumber手机号
        return $dataObj;
    }

    /**
     *微信公众号获取access_token
     */
    protected function accessToken()
    {
        $appid = Env::get('appid');
        $secret = Env::get('appsecret');
        if (!$appid || !$secret) {
            $this->error('请在.env配置参数');
        }
        $grant_type = 'client_credential';
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=' . $grant_type . '&appid=' . $appid . '&secret=' . $secret;
        return $this->curlGet($url);
    }

    

}