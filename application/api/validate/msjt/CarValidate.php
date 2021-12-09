<?php


namespace app\api\validate\msjt;


use app\api\validate\PublicValidate;

class CarValidate extends PublicValidate
{
    protected $rule = [
        'goods_id' => 'require',
        'sku_id' => 'require',
        'num' => 'require',
    ];

    protected $message = [
        'goods_id.require' => '请选择商品',
        'sku_id.require' => '请选择规格',
        'num.require' => '请添加数量'
    ];
}