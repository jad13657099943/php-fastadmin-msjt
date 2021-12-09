<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Hot;
use app\api\model\msjt\Search;
use app\api\services\PublicService;

class SearchService extends PublicService
{
    /**
     * 搜素记录
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function list($uid, $params)
    {
        $where['user_id'] = $uid;
        $where['type'] = 1;
        if (!empty($params['type'])) $where['type'] = $params['type'];
        $list = Search::wherePaginate($where, '*', $params['limit'] ?? 10);
        return $this->success('搜索记录', $list);
    }

    /**
     * 记录搜索
     * @param $uid
     * @param $title
     */
    public function search($uid, $title, $type = 1)
    {
        $id = Search::whereValue(['user_id' => $uid, 'title' => $title, 'type' => $type], 'id');
        if ($id) return;
        Search::whereInsert(['user_id' => $uid, 'title' => $title, 'type' => $type]);
    }

    /**
     * 记录搜索次数
     * @param $title
     * @param int $type
     * @throws \think\Exception
     */
    public function addHotNum($title, $type = 1)
    {
        $id = Hot::flexibleSql([
            'where' => ['title' => $title, 'type' => $type],
            'ending' => ['type' => 'value', 'field' => 'id']
        ]);
        if ($id) {
            Hot::whereSetInc(['id' => $id], 'num', 1);
        } else {
            Hot::whereInsert(['title' => $title, 'type' => $type]);
        }
    }

    /**
     * 猜你喜欢
     * @param $params
     * @return false|string
     */
    public function hot($params)
    {
        $where=[];
        if (!empty($params['type'])){
            $where['type']=$params['type'];
        } else{
            $where['type']=1;
        }
        $list = Hot::flexibleSql([
            'where'=>$where,
            'order' => ['field' => 'num', 'order' => 'desc'],
            'ending' => ['type' => 'paginate', 'limit' => $params['limit'] ?? 5]
        ]);
        return $this->success('热度', $list);
    }

    /**
     * 删除搜索记录
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     */
    public function del($uid, $params)
    {
        $where['user_id'] = $uid;
        $where['id'] = ['in', $params['id']];
        $status = Search::whereDel($where);
        return $this->statusReturn($status, '删除成功', '删除失败');
    }

}