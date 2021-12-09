<?php

namespace app\common\model;

use think\Model;


/**
 * 会员模型
 */
class Visit Extends Model
{
    // 开启自动写入时间戳字段
    const LEFT = 'LEFT';
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;


    public function user(){
       return $this->belongsTo('User','user_id','id','', "LEFT")->setEagerlyType(0);
    }

}