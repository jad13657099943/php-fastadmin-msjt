<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Grade;
use app\api\services\PublicService;

class GradeService extends PublicService
{
    /**
     * 等级制度
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function grade()
    {
        $list = Grade::whereSelect();
        return $this->success('等级制度', $list);
    }
}