<?php

namespace app\common\model;

use think\Model;

class UserLevel extends Model
{

    // 表名
    protected $name = 'user_level';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];

}
