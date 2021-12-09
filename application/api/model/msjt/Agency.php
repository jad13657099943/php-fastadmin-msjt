<?php


namespace app\api\model\msjt;


use app\api\model\Kernel;

class Agency extends Kernel
{
    protected $name='msjt_users_order_agency';

    public function user(){
        return $this->hasOne(Users::class,'id','user_id')->field('id,nickname,head_image');
    }
}