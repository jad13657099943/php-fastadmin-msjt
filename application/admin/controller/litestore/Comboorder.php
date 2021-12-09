<?php

namespace app\admin\controller\litestore;

use app\api\controller\Writeoff;
use app\common\controller\Backend;
use app\common\model\User;
use app\common\model\UserRebate;
use fast\Date;
use app\api\controller\Pay;


/**
 *
 *
 * @icon fa fa-circle-o
 */
class Comboorder extends Backend
{

    protected $noNeedLogin = ['getExpressCompany'];
    protected $noNeedRight = [];
    /**
     * Litestoreorder模型对象
     * @var \app\admin\model\Litestoreorder
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Litestoreorder');
        $this->view->assign("payStatusList", $this->model->getPayStatusList());
        $this->view->assign("freightStatusList", $this->model->getFreightStatusList());
        $this->view->assign("receiptStatusList", $this->model->getReceiptStatusList());
        $this->view->assign("orderStatusList", $this->model->getOrderStatusList());
    }


    /**
     * 查看
     */
    public function index()
    {
        $param = $this->request->get('order_status');

        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);

        if ($this->request->isAjax()) {

            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
                return $this->selectpage();


            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
//dump($where);die;
            $wheres = [
                'type' => ['in', '1,2'],
                'pay_status' => 20,
            ];

            $total = $this->model
                ->with(['address', 'users'])
                ->where($wheres)
                ->where($where)
                ->order($sort, $order)
                ->count();

            $field = 'id,order_no,type,is_status,order_status,total_price,order_type,current_frequency
                      ,pay_price,consignee,reserved_telephone,nickname,mobile,createtime,ship_time';
            $list = $this->model
                ->field($field)
                ->with(['address' => function ($query) {
                    $query->field('order_id,name,phone,site');

                }, 'users' => function ($query) {
                    $query->field('pid');
                }])
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            // $list = collection($list)->toArray();
            // if ($list != null) {
            $user_model = model("User");
            foreach ($list as $k => $v) {
                unset($list[$k]['address'], $list[$k]['users']);
                if ($v['pid']) {
                    $info = $user_model->find_data(['id' => $v['pid']], 'username');
                    $list[$k]['username'] = $info['username'];
                } else {
                    $list[$k]['username'] = "/";
                }
            }
            //  }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        //统计订单数量
        $rows = $this->model->statisticsCount(10);
        $this->view->assign('row', $rows);
        return $this->view->fetch();
    }


    public function detail($ids = '')
    {
        if ($this->request->isAjax()) {

            $model = model('common/Litestoreordership');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $wheres = ['order_id' => $ids];
            $total = $model
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->count();//echo $this->model->getLastSql(); exit;

            $list = $model
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->assign('ids', $ids);
        return $this->fetch();
    }

    public function write_off($ids = '')
    {
        if ($this->request->isAjax()) {

            $model = model('common/Litestoreorderwriteoff');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $wheres = ['order_id' => $ids];
            $total = $model
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->count();//echo $this->model->getLastSql(); exit;

            $list = $model
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->assign('ids', $ids);
        return $this->fetch();
    }

    public function ship($ids = '')
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                try {
                    if ($row->total_frequency == 1) {
                        $row->freight_status = 20;
                    }
                    ++$row->current_frequency;
                    if ($row->current_frequency == $row->total_frequency) {
                        $row->ship_time = 0;
                        $row->order_status = 50;
                    } else {

                        $row->ship_time += $row->time_interval * Date::DAY;
                        $row->order_status = 30;
                    }
                    $rebate = UserRebate::getByUid($row->user_id);
                    if ($rebate) {
                        Writeoff::commission(User::get($rebate->first_id));
                    }
                    $result = model('common/Litestoreordership')->allowField(true)->save($params);
                    if ($result !== false && $row->save()) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                } catch (\think\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->assign('company', model('common/kdniao')->column('company as id,company'));
        $row->address;
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    private function getLevel($level)
    {
        return config('site.vip' . $level . '_interval');
    }

    public function refund()
    {
        if ($this->request->isAjax()) {
            $ids = $this->request->param('ids');
            $remark = $this->request->param('remark');
            $row = $this->model->get($ids);

            $row->examine_time = time();
            $row->remark = $remark;
            $row->order_status = 0;
            $row->pay_status = 30;
            $this->model->startTrans();
            try {
                $row->money = $row->pay_price;
                $row->refund_no = $row->order_no . '1';
                $result = Pay::refund($row, $row);
                unset($row->money, $row->refund_no);

                //修改用户信息
                $user_info = User::get($row->user_id);
                if ($user_info->score >= $row->pay_price) {
                    User::score($row->pay_price, $row->user_id, '会员套餐退款');
                }

                $user_info->vip_type = 0;
                $user_info->pid = 0;
                $user_info->is_buy_ordinary_vip = 0;
                $arr = explode(',', $user_info->buy_vip_goods);
                $index = array_search($row->goods[0]->goods_id, $arr);
                if ($arr && $index !== false) {
                    unset($arr[$index]);
                    $user_info->buy_vip_goods = implode(',', $arr);
                }

                if ($row->save() && $result && $user_info->save()) {
                    $this->model->commit();
                    $this->success('审核成功');
                }
                $this->model->rollback();
                $this->error('审核失败');
            } catch (GatewayException $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }

        }
    }

    /**
     * 导出订单数据
     * @param string $ids
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function out($ids = "")
    {
        $params = $this->request->request();
//        $filter = $this->request->get("filter", '');
//        dump($filter);die;

//        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
//        dump($where);die;
        $ids != 'all' && $where['litestoreorder.id'] = ['in', $ids];

        $filter = json_decode($params['filter'], true);
        $op = json_decode($params['op'], true);


        //基础条件
        $ids == "all" &&
        $where = ['type' => ['in', '1,2'], 'pay_status' => 20,];

        //拼接搜索条件
        foreach ($filter as $k => $v) {
            if ($op[$k] == 'RANGE') {
                $k = 'litestoreorder.' . $k;
                $op[$k] = 'between';
                $v = [strtotime(explode(' - ', $v)['0']), strtotime(explode(' - ', $v)['1'])];
            }
            $op[$k] == 'LIKE' && $v = '%' . $v . '%';

            $where[$k] = [$op[$k], $v];
        }

        //数据集
        $result = $this->model->with(['address', 'goods', 'user'])->where($where)->order('id desc')->select();

        out2($result, '套餐订单数据');
    }
}
