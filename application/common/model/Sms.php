<?php

namespace app\common\model;

use think\Model;

/**
 * 短信验证码
 */
class Sms Extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    public function check_code($mobile, $type = 1)
    {
        if (empty($mobile)) {

            return false;
        }
        $where = ['mobile' => $mobile, 'type' => $type];
        $code_info = $this->where($where)->find();
        if ($code_info) {

            return $code_info;
        }
        return false;
    }
}
