<?php


namespace app\api\validate;



use think\Exception;
use think\Validate;

class PublicValidate extends Validate
{

    public function isCheck($params){
        if (!$this->check($params)){
           $msg=$this->getError();
          throw new Exception($msg,'500');
        };
    }

}