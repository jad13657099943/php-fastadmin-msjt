<?php

namespace app\api\controller;

use app\common\controller\Api;
use fast\Random;

/**
 * Token接口
 */
class Token extends Api
{

    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 检测Token是否过期
     *
     */
    public function check()
    {
        $token = $this->auth->getToken();
        $tokenInfo = \app\common\library\Token::get($token);
        $this->success('', ['token' => $tokenInfo['token'], 'expires_in' => $tokenInfo['expires_in']]);
    }

    /**
     * 刷新Token
     *
     */
    public function refresh()
    {
        //删除源Token
        $token = $this->auth->getToken();
        \app\common\library\Token::delete($token);
        //创建新Token
        $token = Random::uuid();
        \app\common\library\Token::set($token, $this->auth->id, 259200000);
        $tokenInfo = \app\common\library\Token::get($token);
        $this->success('', ['token' => $tokenInfo['token'], 'expires_in' => $tokenInfo['expires_in']]);
    }

    /*
     * 创建新token并返回
     */
    public function create()
    {
        //创建新Token
        $token = Random::uuid();
        \app\common\library\Token::set($token, $this->auth->id, 259200000);
        $tokenInfo = \app\common\library\Token::get($token);
        return $tokenInfo;
    }

}
