<?php


namespace app\api\services;


use think\Exception;
use think\Request;

class AuthService extends PublicService
{
    private $keeptime = 2592000;
    private $auth='bll';

    /**
     * 生成token
     * @param $uid
     * @return string
     */
    protected function getToken($uid){
       $array=[
          'auth'=>$this->auth,
          'uid'=>$uid,
          'exp'=>time()+$this->keeptime
        ];
        return urlencode(base64_encode(json_encode($array)));
    }

    /**
     * 验证返回
     * @return mixed
     * @throws \think\Exception
     */
    protected function checkToken(){
        $token= Request::instance()->header('token');
        if (empty($token)){
            error('请登录');
        }
        try {
            $array= json_decode(base64_decode(urldecode($token)));
        }catch (Exception $exception){
            error('请重新登录');
        }
        if ($array->exp<time()||empty($array->uid)){
            error('请重新登录');
        }
        return $array;
    }

    /**
     * 获取uid
     * @return mixed
     */
    protected function user(){
       $token= Request::instance()->header('token');
       $array= json_decode(base64_decode(urldecode($token)));
       return $array;
    }


}