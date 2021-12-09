<?php

namespace app\admin\controller\litestore;

use app\common\controller\Backend;
use app\admin\model\AuthRule;
use PHPExcel_Exception;
use PHPExcel_Writer_Exception;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Litestoreclusterorder extends Backend
{

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
        // $param = $this->request->param(); dump($param); exit;

    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;

        //设置过滤方法
        $this->request->filter(['strip_tags']);


        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $wheres['litestoreorder.order_type'] = '20';
//            $wheres['litestoreorder.is_del'] = '0'; //后台显示用户已删除订单
            // $wheres['litestoreorder.order_status'] = ['neq','0'];
            $total = $this->model
                ->with(['address'])
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->count();

            $field = 'id,order_no,pay_status,pay_time,express_price,order_status,total_price,order_type,refund_status,
                      coupon_price,total_num,remark,freight_price,nickname,mobile,is_status,pay_price,consignee,reserved_telephone';
            $list = $this->model
                ->field($field)
                ->where($where)
                ->where($wheres)
                ->with(['address' => function ($query) {
                    $query->field('order_id,name,phone,site');
                }, 'goods' => function ($query) {
                    $query->field('order_id,goods_name,key_name,goods_price,total_num,images,total_price');
                }/*, 'users' => function ($query) {
                    $query->field('nickname,mobile');
                }*/])
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $k => $row) {
                unset($list[$k]['address'], $list[$k]['users']);
            }

//                foreach ($list as $row) {
//                    $row->visible(['id', 'order_no', 'total_price', 'pay_price', 'pay_time', 'express_price', 'freight_time', 'receipt_time', 'order_status', 'createtime']);
//                    $row->visible(['address']);
//                    $row->getRelation('address')->visible(['name']);
//                }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        //统计订单数量
        $rows = $this->model->statisticsCount(20);
        $this->view->assign('row', $rows);
        return $this->view->fetch();
    }

    /**
     * 导出订单数据
     * * @param string $ids
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function out($ids = '')
    {
        $where = ['order_type' => '20'];
        $ids != 'all' && $where['litestoreorder.id'] = ['in', $ids];
        out2($this->model, $where, '拼团订单数据');
    }

}
