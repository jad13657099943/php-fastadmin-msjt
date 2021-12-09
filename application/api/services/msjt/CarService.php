<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Car;
use app\api\services\PublicService;

class CarService extends PublicService
{
    /**
     * 购物车
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function lists($uid, $params)
    {
        $with = 'goods';
        $where['user_id'] = $uid;
        $list = Car::whereWithPaginate($with, $where, '*', $params['limit'] ?? 10);
        foreach ($list->items() as $item) {
            $item->sku = json_decode($this->getKeyValue($item->goods['configjson'], 'id', $item->sku_id), true);
        }
        return $this->success('购物车', $list);
    }

    /**
     * 加入购物车
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     */
    public function add($uid, $params)
    {
        if ($this->isExistCar($uid, $params)) {
            $status = Car::whereInsert([
                'user_id' => $uid,
                'goods_id' => $params['goods_id'],
                'sku_id' => $params['sku_id'],
                'num' => $params['num']
            ]);
        } else {
            $status = Car::whereSetInc(['user_id' => $uid, 'goods_id' => $params['goods_id'], 'sku_id' => $params['sku_id']], 'num', $params['num']);
        }

        return $this->statusReturn($status, '加入购物车成功', '加入购物车失败');
    }

    /**
     * 购物车是否存在
     * @param $uid
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isExistCar($uid, $params)
    {
        $info = Car::whereFind(['user_id' => $uid, 'goods_id' => $params['goods_id'], 'sku_id' => $params['sku_id']]);
        if (empty($info)) {
            return true;
        }
        return false;
    }

    /**
     * 删除购物车
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     */

    public function del($uid, $params)
    {
        $this->changeIds($params);
        $where['user_id'] = $uid;
        $where['id'] = ['in', $params['ids']];
        $status = Car::whereDel($where);
        return $this->statusReturn($status, '删除成功', '删除失败');
    }

    /**
     * 数组
     * @param $params
     * @throws \think\Exception
     */
    public function changeIds($params)
    {
        if (!is_array($params['ids'])) {
            $this->error('参数错误');
        }
    }

    /**
     * 编辑购物车
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     */
    public function edit($uid, $params)
    {
        $this->checkNum($params);
        Car::whereUpdate(
            ['user_id' => $uid, 'id' => $params['id']],
            ['num' => $params['num']]
        );
        return $this->success('修改成功');
    }

    /**
     * 验证数量
     * @param $params
     * @throws \think\Exception
     */
    public function checkNum($params)
    {
        if ($params['num'] < 1) {
            $this->error('数量至少为1');
        }
    }
}