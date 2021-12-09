<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Region;
use app\api\services\PublicService;
use think\Db;

class RegionService extends PublicService
{

    /**
     * 添加地址
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     */
    public function set($uid, $params)
    {
        $params['user_id'] = $uid;
        $status = Region::whereInsert($params);
        $this->isDefault($uid,$status, $params['is_default']);
        return $this->statusReturn($status, '添加成功', '添加失败');
    }

    /**
     * 是否默认提交
     * @param $id
     * @param $is_default
     * @throws \think\Exception
     */
    public function isDefault($uid,$id, $is_default)
    {
        if ($is_default == 2) {
            $this->setDefault($uid,$id);
        }
    }

    /**
     * 设置默认地址
     * @param $id
     * @return false|string
     * @throws \think\Exception
     */
    public function setDefault($uid,$id)
    {
        return Db::transaction(function () use ($uid,$id) {
            Region::whereUpdate(['id' => $id,'user_id'=>$uid], ['is_default' => 2]);
            Region::whereUpdate(['id' => ['<>', $id],'user_id'=>$uid], ['is_default' => 1]);
            return $this->success('设置成功');
        });

    }


    /**
     * 编辑地址
     * @param $id
     * @param $params
     * @return false|string
     * @throws \think\Exception
     */
    public function update($uid,$id, $params)
    {
        unset($params['id']);
        $status = Region::whereUpdate(['id' => $id], $params);
        $this->isDefault($uid,$id, $params['is_default']);
        return $this->statusReturn($status, '修改成功', '修改失败');
    }

    /**
     * 删除地址
     * @param $id
     * @return false|string
     * @throws \think\Exception
     */
    public function del($id)
    {
        $status = Region::whereDel(['id' => $id]);
        return $this->statusReturn($status, '删除成功', '删除失败');
    }

    /**
     * 地址列表
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function lists($uid, $params)
    {
        $where['user_id'] = $uid;
        $list = Region::wherePaginate($where, '*', $params['limit'] ?? 10, 'is_default');
        return $this->success('地址', $list);
    }
}