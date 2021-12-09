<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Study;
use app\api\services\PublicService;

class StudyService extends PublicService
{
    /**
     * 记录学习人数
     * @param $uid
     * @param $params
     */
    public function addStudyNum($uid, $params)
    {
        $where['user_id'] = $uid;
        $where['curriculum_id'] = $params['id'];
        $id = Study::flexibleSql([
            'where' => $where,
            'ending' => ['type' => 'value', 'field' => 'id']
        ]);
        if ($id) return;
        Study::whereInsert($where);
    }

    /**
     * 学习人数
     * @param $id
     * @return int|string
     * @throws \think\Exception
     */
    public function getStudyNum($id)
    {
        return Study::whereCount(['curriculum_id' => $id]);
    }
}