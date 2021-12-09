<?php


namespace app\api\model\msjt;


use app\api\model\Kernel;

class Car extends Kernel
{
    protected  $name='msjt_users_car';

    public function goods(){
        return $this->hasOne(Goods::class,'id','goods_id')->field('id,name,simages,configjson');
    }

}