<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Litestoreorderrefund extends Model
{
    // 表名
    protected $name = 'litestore_order_refund';

    use SoftDelete;
    protected $deleteTime = 'delete_time';

    protected $updateTime = false;

    public function liteStoreOrder()
    {
        return $this->hasOne('Litestoreorder', 'order_no', 'order_no')->setEagerlyType(0);
    }



    public function users()
    {
        return $this->hasOne('User', 'id', 'uid', [], 'LEFT')->setEagerlyType(0);
    }

}
