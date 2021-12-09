<?php


namespace app\api\model\msjt;


use app\api\model\Kernel;

class Sale extends Kernel
{
    protected $name='msjt_users_sale';

    public function goods(){
        return $this->hasMany(OrderGoods::class,'order_no','order_no')->where('status',2);
    }

    public function orders(){
        return $this->hasOne(Order::class,'order_no','order_no');
    }
}