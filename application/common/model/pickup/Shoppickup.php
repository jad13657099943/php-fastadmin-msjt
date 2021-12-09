<?php

namespace app\admin\model\pickup;

use think\Model;

class Shoppickup extends Model
{
    // 表名
    protected $name = 'pickup_shop';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [

    ];
    public function select_data($where,$field ,$order = 'id desc', $page = 0, $pagesize = 10){
        return $this->where($where)->field($field)->order($order)->limit(($page - 1) * $pagesize, $pagesize)->select();
    }

    







}
