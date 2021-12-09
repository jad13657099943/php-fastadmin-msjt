<?php


namespace app\api\controller\msjt;


use app\api\services\CommonService;
use think\Request;

class Config extends CommonService
{
    /**
     * 基础配置
     * @param Request $request
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function config(Request $request){
        $params=$request->param();
        $list= \app\api\model\msjt\Config::where('title','in',$params['name'])->select();
        return $this->success('基础配置',$list);
    }
}