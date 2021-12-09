<?php


namespace app\api\services\msjt;


use app\api\model\msjt\CurriculumType;
use app\api\model\msjt\Type;
use app\api\services\PublicService;

class TypeService extends PublicService
{
    /**
     * 商品分类
     * @param $params
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsType($params)
    {
        $where['status'] = 1;
        $where['pid'] = '';
        if (!empty($params['is_recommend_data'])) {
            $where['is_recommend_data'] = $params['is_recommend_data'];
        }
        if (!empty($params['pid'])) {
            $where['pid'] = $params['pid'];
        }
        $list = Type::whereSelect($where, '*', 'weigh');
        return $this->success('商品分类', $list);
    }

    /**
     * 课程分类
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function curriculumType($params)
    {
        $where['status'] = 1;
        $where['pid'] = '';
        if (!empty($params['is_recommend_data'])) {
            $where['is_recommend_data'] = $params['is_recommend_data'];
        }
        if (!empty($params['pid'])) {
            $where['pid'] = $params['pid'];
        }
        $list = CurriculumType::whereSelect($where, '*', 'weigh');
        return $this->success('商品分类', $list);
    }
}