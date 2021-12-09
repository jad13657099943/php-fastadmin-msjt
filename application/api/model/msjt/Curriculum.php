<?php


namespace app\api\model\msjt;


use app\api\model\Kernel;

class Curriculum extends Kernel
{
    protected $name='msjt_goods_curriculum';

    public function video(){
      return $this->hasMany(Video::class,'curriculum_id','id')->where('status',1)->order('weigh','desc');
    }
}