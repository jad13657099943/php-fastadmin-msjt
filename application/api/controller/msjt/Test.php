<?php


namespace app\api\controller\msjt;


use think\Request;

class Test
{
    public function test(Request $request)
    {
        return \app\api\model\msjt\Users::flexibleSql(
            [

                'field'=>'id,nickname',
               // 'order'=>['field'=>'id','order'=>'desc'],
                'ending' => ['type' => 'select']
            ]);
    }
}