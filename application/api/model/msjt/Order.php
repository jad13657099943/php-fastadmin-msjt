<?php


namespace app\api\model\msjt;


use app\api\model\Kernel;

class Order extends Kernel
{
    protected $name='msjt_users_order';

    public function goods(){
        return $this->hasMany(OrderGoods::class,'order_no','order_no')->where('status',1);
    }

    public function sale(){
        return $this->hasOne(Sale::class,'order_no','order_no');
    }
}