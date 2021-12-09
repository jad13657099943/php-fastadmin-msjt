<?php


namespace app\api\validate\msjt;


use app\api\validate\PublicValidate;

class RegionValidate extends PublicValidate
{
    protected $rule=[
        'name'=>'require',
        'mobile'=>'require',
        'region'=>'require',
        'content'=>'require',
    ];

    protected $message=[
        'name.require'=>'参数错误',
        'mobile.require'=>'参数错误',
        'region.require'=>'参数错误',
        'content.require'=>'参数错误',
    ];
}