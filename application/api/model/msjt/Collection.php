<?php


namespace app\api\model\msjt;


use app\api\model\Kernel;

class Collection extends Kernel
{
    protected $name='msjt_users_collection';

    public function goods(){
        return $this->hasOne(Goods::class,'id','collection_id');
    }

    public function curriculum(){
        return $this->hasOne(Curriculum::class,'id','collection_id');
    }
}