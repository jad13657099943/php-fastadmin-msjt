<?php


namespace app\admin\model\msjt\users;


use think\Model;

class OrderGoods extends Model
{
    // 表名
    protected $name = 'msjt_users_order_goods';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
}