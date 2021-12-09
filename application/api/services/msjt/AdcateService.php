<?php


namespace app\api\services\msjt;


use app\api\model\cms\Adcate;
use app\api\model\cms\Block;
use app\api\services\PublicService;

class AdcateService extends PublicService
{
    /**
     * 广告
     * @param $params
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function list($params)
    {
        $where = [];
        if (!empty($params['name'])) $where['name'] = $params['name'];
        $info = Adcate::whereFind($where);
        $info['list'] = Block::whereSelect(['cate_id' => $info->id]);
        return $this->success('广告', $info);
    }
}