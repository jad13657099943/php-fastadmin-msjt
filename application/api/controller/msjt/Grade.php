<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\GradeService;
use app\api\services\traits\Auth;

class Grade
{
    use Auth;

    /**
     * 等级制度
     * @param GradeService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function grade(GradeService $service)
    {
        $this->checkToken();
        return $service->grade();
    }
}