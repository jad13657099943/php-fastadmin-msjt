<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Site;
use app\api\services\PublicService;

class SiteService extends PublicService
{
    /**
     * 地址列表
     * @param string $id
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function site($params)
    {
        $where = [];
        $where['status'] = 1;
        $where['pid'] = ['NULL'];
        if ($params['id']) $where['pid'] = $params['id'];
        if (!empty($params['name'])) $where['name'] = ['like', '%' . $params['name'] . '%'];
        $list = Site::whereSelect($where);
        return $this->success('地址', $list);
    }
}