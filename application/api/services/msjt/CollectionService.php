<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Collection;
use app\api\services\PublicService;

class CollectionService extends PublicService
{

    /**
     * 添加收藏
     * @param $uid
     * @param $params
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add($uid, $params)
    {
        $data = [
            'user_id' => $uid, 'type' => $params['type'], 'collection_id' => $params['collection_id']
        ];
        $model = Collection::whereFind($data);
        if (!empty($model)) {

            return $this->success('收藏成功');

        } else {
           $data= Collection::whereInsert($data);
        }

        return $this->success('收藏成功',$data);
    }

    /**
     * 删除收藏
     * @param $uid
     * @param $params
     * @return false|string
     */
    public function del($uid, $params)
    {
        $where['user_id']=$uid;
        $where['id']=['in',$params['id']];
        $status = Collection::whereDel($where);
        return $this->statusReturn($status, '取消收藏成功', '取消收藏失败');
    }

    /**
     * 收藏列表
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function lists($uid, $params)
    {
        $where['user_id'] = $uid;
        if (!empty($params['type'])) {
            $where['type'] = $params['type'];
            $with = $this->getWith($params['type']);
        }
        $list = Collection::whereWithPaginate($with, $where, '*', $params['limit'] ?? 10);
        return $this->success('收藏列表', $list);
    }

    /**
     * 收藏类型
     * @param $type
     * @return string
     */
    public function getWith($type)
    {
        if ($type == 1) {
            return $with = 'goods';
        } else {
            return $with = 'curriculum';
        }
    }

}