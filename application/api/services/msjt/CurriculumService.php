<?php


namespace app\api\services\msjt;


use addons\epay\library\Service;
use app\api\model\msjt\Collection;
use app\api\model\msjt\Curriculum;
use app\api\model\msjt\CurriculumOrder;
use app\api\model\msjt\CurriculumSign;
use app\api\model\msjt\Study;
use app\api\model\msjt\Users;

use app\api\services\msjt\common\AgencyOrderService;
use app\api\services\PublicService;
use think\Db;
use think\Request;

class CurriculumService extends PublicService
{
    /**
     * 课程列表
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function lists($uid, $params)
    {
        $where['status'] = 1;


        if (!empty($params['type_id'])) $where['type_id'] = $params['type_id'];

        if (!empty($params['name'])) {

            $service = new SearchService();
            if (!empty($uid)) {
                $service->search($uid, $params['name'], 2);
            }
            $service->addHotNum($params['name'], 2);
            $where['name'] = ['like', '%' . $params['name'] . '%'];
        }

        if (!empty($params['statedata'])) {
            $where['statedata'] = $params['statedata'];
        }

        if (!empty($params['is_recommend_data'])) {
            $where['is_recommend_data'] = $params['is_recommend_data'];
        }

        if (!empty($params['is_hot'])) {
            $where['is_hot'] = $params['is_hot'];
        }

        $filed = 'id,name,type_id,simages,money,look_num,createtime,statedata,brief';
        $list = Curriculum::wherePaginate($where, $filed, $params['limit'] ?? 10, 'weigh');


        $Num = new NumService();
        $Study = new StudyService();

        foreach ($list as $item) {
            $item->look_num += $Num->curriculumNum($item->id, $item->statedata);
            $item->study_num = $Study->getStudyNum($item->id);
        }

        return $this->success('课程列表', $list);
    }

    /**
     * 课程详情
     * @param $params
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail($uid, $params)
    {
        $with = 'video';
        $info = Curriculum::whereWithFind($with, ['id' => $params['id']]);
        $info['configjson'] = json_decode($info['configjson'], true);
        if (empty($uid)) {
            $info['collection'] = 0;
            $info['is_buy'] = 0;
        } else {
            $info['collection'] = Collection::whereFind(['user_id' => $uid, 'type' => 2, 'collection_id' => $params['id']], 'id')->id;
            $info['is_buy'] = CurriculumOrder::whereValue(['user_id' => $uid, 'curriculum_id' => $params['id'], 'status' => 2], 'id');
        }
        $service = new NumService();
        $info['look_num'] += $service->curriculumNum($params['id'], $info['statedata']);
        return $this->success('课程详情', $info);
    }


    /**
     * 下单
     * @param $uid
     * @param $params
     * @return mixed
     */
    public function setOder($uid, $params)
    {
        return Db::transaction(function () use ($uid, $params) {
            $order_no = $this->orderNo('A');
            $info = $this->checkCurriculum($uid, $params['id']);
            $price_text = $this->getPriceText($uid);
            $status = CurriculumOrder::whereInsert([
                'user_id' => $uid,
                'order_no' => $order_no,
                'curriculum_id' => $params['id'],
                'info' => json_encode($info),
                'money' => $info->$price_text
            ]);
            return $this->statusReturn($status, '下单成功', '下单失败', ['order_no', $order_no]);
        });
    }


    /**
     * 验证课程
     * @param $id
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkCurriculum($uid, $id, $type = 1)
    {
        $info = Curriculum::whereFind(['id' => $id]);
        if (empty($info) || $info->status == 2) $this->error('课程失效');
        if ($type == 1) {
            if ($info->statedata != 1) $this->error('非线上课程');
            $this->checkBuy($uid, $id);
        } else {
            if ($info->statedata != 2) $this->error('非线下课程');
        }

        return $info;
    }

    /**
     * 验证是否购买过
     * @param $uid
     * @param $id
     * @throws \think\Exception
     */
    public function checkBuy($uid, $id)
    {
        $id = CurriculumOrder::whereValue(['user_id' => $uid, 'curriculum_id' => $id, 'status' => 2], 'id');
        if ($id) $this->error('已购买,请勿重复购买');
    }

    /**
     * 获取身份价格
     * @param $uid
     * @return string
     */
    private function getPriceText($uid)
    {
        if ($this->isVip($uid)) {
            $price_text = 'user_money';
        } else {
            $price_text = 'money';
        }
        return $price_text;
    }

    /**
     * 是否vip
     * @param $uid
     * @return bool
     */
    private function isVip($uid)
    {
        $grade = Users::whereValue(['id' => $uid], 'grade');
        if ($grade > 1) return true;
        return false;
    }

    /**
     * 课程支付
     * @param $uid
     * @param $order_no
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pay($uid, $order_no)
    {
        $info = $this->checkOrder($uid, $order_no);
        $openid = Users::whereValue(['id' => $uid], 'openid');
        $params = [
            'amount' => $info->money,
            'orderid' => $info->order_no,
            'type' => 'wechat',
            'title' => '购买课程',
            'notifyurl' => Request::instance()->root(true) . '/curriculum/notifyurl',
            'method' => 'mp',
            'openid' => $openid,
        ];
        $data = Service::submitOrder($params);
        return $this->success('wx支付', $data);
    }

    /**
     * 验证订单
     * @param $uid
     * @param $order_no
     * @return array|bool|\PDOStatement|string|\think\Model|null
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkOrder($uid, $order_no)
    {
        $info = CurriculumOrder::whereFind(['user_id' => $uid, 'order_no' => $order_no]);
        if (empty($info) || $info->status != 1 || $info->money <= 0) $this->error('订单异常');
        return $info;
    }


    /**
     * 支付回调
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function notifyurl()
    {
        $result = $this->xmlJson();
        $result = $this->isSuccess($result);
        if ($result) {
            $model = CurriculumOrder::whereFind(['order_no' => $result->out_trade_no]);
            if (empty($model) || $model->status != 1) {
                $this->wxSuccess();
                exit();
            }
            $model->pay_money = $result->total_fee / 100;
            $model->status = 2;
            $model->pay_time = strtotime($result->time_end);
            $model->save();
            $service = new AgencyOrderService();
            $service->addAgencyOrder($result->out_trade_no, 2, 2);
            $this->wxSuccess();
        }
    }


    /**
     * 课程报名
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sign($uid, $params)
    {
        $this->checkSign($uid, $params['id']);

        $info = $this->checkCurriculum($uid, $params['id'], 2);

        $status = CurriculumSign::whereInsert([
            'user_id' => $uid,
            'curriculum_id' => $params['id'],
            'info' => json_encode($info),
            'name' => $params['name'],
            'mobile' => $params['mobile'],
            'card' => $params['card']
        ]);

        return $this->statusReturn($status, '报名成功', '报名失败');
    }

    /**
     * 验证是否已报名
     * @param $uid
     * @param $id
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkSign($uid, $id)
    {
        $info = CurriculumSign::whereFind(['user_id' => $uid, 'curriculum_id' => $id]);
        if (!empty($info)) $this->error('请勿重复报名');
    }


}