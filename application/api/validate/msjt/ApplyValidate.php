<?php


namespace app\api\validate\msjt;


use app\api\validate\PublicValidate;

class ApplyValidate extends PublicValidate
{
    protected $rule = [
        'name' => 'require',
        'mobile' => 'require',
    ];

    protected $message = [
        'name.require' => '请填写姓名',
        'mobile.require' => '请填写手机号',
    ];
}