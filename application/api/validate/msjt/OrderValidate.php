<?php


namespace app\api\validate\msjt;


use app\api\validate\PublicValidate;

class OrderValidate extends PublicValidate
{
    protected $rule=[
        'site'=>'require',
    ];

    protected $message=[
        'site.require'=>'请选择地址',
    ];
}