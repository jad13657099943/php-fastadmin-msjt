<?php


namespace app\api\services\traits;


use think\Exception;
use think\Request;

trait Auth
{
    protected $keeptime = 2592000;
    protected $auth = 'msjt';

    /**
     * 生成token
     * @param $uid
     * @return string
     */
    protected function getToken($uid)
    {
        $array = [
            'auth' => $this->auth,
            'uid' => $uid,
            'exp' => time() + $this->keeptime
        ];
        return urlencode(base64_encode(json_encode($array)));
    }

    /**
     * 验证返回
     * @return mixed
     * @throws \think\Exception
     */
    protected function checkToken($type = 'Need')
    {

        $token = Request::instance()->header('token');
        if ($type == 'noNeed') {
            if (empty($token)) {
                return $this->noNeed();
            } else {
                try {
                    return $array = json_decode(base64_decode(urldecode($token)));
                } catch (Exception $exception) {
                    return $this->noNeed();
                }
            }
        }
        if (empty($token)) {
            throw new Exception('请登录', 401);
        }
        try {
            $array = json_decode(base64_decode(urldecode($token)));
        } catch (Exception $exception) {
            throw new Exception('请重新登录', 401);
        }
        if ($array->exp < time() || empty($array->uid)) {
            throw new Exception('请重新登录', 401);
        }
        return $array;
    }

    /**
     * 无需登录
     * @return mixed
     */
    protected function noNeed()
    {
        $data = [
            'uid' => 0
        ];
        return json_decode(json_encode($data));
    }

    /**
     * 获取uid
     * @return mixed
     */
    protected function user()
    {
        $token = Request::instance()->header('token');
        $array = json_decode(base64_decode(urldecode($token)));
        return $array;
    }
}