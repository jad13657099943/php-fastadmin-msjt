<?php


namespace app\api\services\msjt\take;


use app\api\model\msjt\CurriculumOrder;
use app\api\model\msjt\Grade;
use app\api\model\msjt\Order;
use app\api\model\msjt\Users;

class UpgradeService
{
    private $uid;


    public function __construct($uid)
    {
        $this->uid = $uid;
    }

    /**
     * 升级
     * @param $uid
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function upgrade()
    {
        $gradeArray = $this->getGradeRule();
        $num = $this->orderSum();
        $grade = $this->getGrade();
        if (!empty($gradeArray[$grade + 1]) && $num >= $gradeArray[$grade + 1]) {
            $this->setGrade($grade + 1);
        }
    }

    /**
     * 更新等级
     * @param $uid
     * @param $grade
     */
    private function setGrade($grade)
    {
        $where['id'] = $this->uid;
        Users::whereUpdate($where, ['grade' => $grade]);
    }

    /**
     * 等级制度
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getGradeRule()
    {
        $list = Grade::whereSelect([], 'id,money');
        foreach ($list as $item) {
            $data[$item->id] = $item->money;
        }
        return $data;
    }

    /**
     * 获取等级
     * @param $uid
     * @return bool
     */
    private function getGrade()
    {
        $where['id'] = $this->uid;
        return Users::whereValue($where, 'grade');
    }

    /**
     * 计算消费金额
     * @param $uid
     * @return float|int|string
     */
    private function orderSum()
    {
        $where['user_id'] = $this->uid;
        $where['status'] = 6;
        $goods_money = Order::whereSum($where, 'money');
        $where['status'] = 2;
        $curriculum_money = CurriculumOrder::whereSum($where, 'money');
        return $goods_money + $curriculum_money;
    }
}