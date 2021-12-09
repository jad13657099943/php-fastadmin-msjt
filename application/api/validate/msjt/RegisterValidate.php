<?php


namespace app\api\validate\msjt;



use app\api\validate\PublicValidate;

class RegisterValidate extends PublicValidate
{
    protected $rule=[
        'code'=>'require',

    ];

    protected $message=[
        'code.require'=>'code错误',
    ];
}