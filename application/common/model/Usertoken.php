<?php

namespace app\common\model;

use think\Model;
use fast\Random;

class Usertoken extends Model
{

    // 表名
    protected $name = 'user_token';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $keeptime = 8640000000;
    // 追加属性
    protected $append = [
    ];

    public function update_token($user_id)
    {
        if (empty($user_id)) {
            return false;
        }
        $token = Random::uuid();
            $data = [
                'token' => $token,
                'user_id' => $user_id,
                'createtime' => time(),
                'expiretime' => $this->keeptime,
            ];
            if (!$this->insert($data)){
                return false;
            }else{
                return $token;
            }
    }
}