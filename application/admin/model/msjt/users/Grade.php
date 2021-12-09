<?php

namespace app\admin\model\msjt\users;

use think\Model;

class Grade extends Model
{
    // 表名
    protected $name = 'msjt_users_grade';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [

    ];
    

    







}
