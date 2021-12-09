<?php

namespace app\admin\controller\apply;

use app\common\controller\Backend;
use app\api\controller\Pay;
use app\common\model\Kdniao;

/**
 * 积分商城订单管理
 *
 * @icon fa fa-circle-o
 */
class Integralorder extends Backend
{

    /**
     * Order模型对象
     * @var \app\admin\model\integral\Order
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();

        $this->model = new \app\admin\model\Integralorder;
        $this->view->assign("isDelList", $this->model->getIsDelList());

        $this->kdniao_model = model('common/Kdniao');
        $companyList = $this->kdniao_model->select_data('', 'company')->toArray();
        foreach ($companyList as $k => $v) {
            $categorydata[$v['company']] = $v;
        }
        $this->view->assign("companyList", $categorydata);
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
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->field('id,order_sn,order_status,image,num,receiver_name,title,integral,add_time')
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    public function edit($ids = null)
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $id = $param['ids'];
            $row = $this->model->get($id);
            $row['receipt_time'] = time();
            $row['delivery_company'] = $param['virtual_name'];
            $row['express_no'] = $param['express_no'];
            $row['shipper_code'] = $this->kdniao_model->where(['company' => $param['virtual_name']])->value('code');
            $row['order_status'] = '1';
            $row->save();
            //获取订单信息
            $user_id = $this->model->where(['id' => $id])->value('uid');
            //发货推送
            $push = new Pay();
            $push->jpush($user_id, 3, $id);
            $this->success();
        }

        $row = $this->model->get($ids);
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

}
