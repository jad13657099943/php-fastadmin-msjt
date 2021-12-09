<?php

namespace app\admin\model\msjt\goods\curriculum;

use app\admin\model\msjt\users\Users;
use think\Model;

class Sign extends Model
{
    // 表名
    protected $name = 'msjt_goods_curriculum_sign';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [

    ];
    

    
  public function user(){
      return $this->hasOne(Users::class,'id','user_id');
  }






}
