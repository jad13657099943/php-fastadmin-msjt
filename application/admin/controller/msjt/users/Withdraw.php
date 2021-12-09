<?php

namespace app\admin\controller\msjt\users;

use app\api\services\msjt\common\AgencyOrderService;
use app\common\controller\Backend;
use think\Db;

/**
 * 提现申请管理
 *
 * @icon fa fa-circle-o
 */
class Withdraw extends Backend
{

    /**
     * Withdraw模型对象
     * @var \app\admin\model\msjt\users\Withdraw
     */
    protected $model = null;
    protected $users = null;
    protected $status = [
        1 => '待审核',
        2 => '已打款',
        3 => '已拒绝'
    ];
    protected $type = [
        1 => '微信',
        2 => '银行卡'
    ];

    public function _initialize()
    {
        parent::_initialize();

        $this->model = new \app\admin\model\msjt\users\Withdraw;
        $this->users = new \app\admin\model\msjt\users\Users();
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    /**
     * 查看
     */
    public function index($ids = '')
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

            $list = $this->model->with('user')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $this->assignconfig('ids', $ids);
        return $this->view->fetch();
    }


    public function info($ids = '')
    {
        $info = $this->model->where('id', $ids)->find();
        $info['user'] = $this->users->where('id', $info->user_id)->find();
        $info['title'] = $info['type'] == 1 ? '微信号' : '银行卡号';
        $info['status'] = $this->status[$info['status']];
        $info['type'] = $this->type[$info['type']];
        $this->view->assign('info', $info);
        return $this->view->fetch();
    }

    public function setSuccess($ids = '')
    {
        $info = $this->model->get($ids);
        if ($info['status'] != 1) $this->error('状态异常');
        $info->status = 2;
        $info->save();
        $this->success('审核成功');

    }

    public function review($ids = '')
    {
        $where['id'] = $ids;
        $info = $this->model->where($where)->find();
        if ($this->request->isPost()) {
            if ($info['status'] != 1) $this->error('状态异常');
            Db::transaction(function ()use($where,$info){
                $params = $this->request->post("row/a");
                $this->model->where($where)->update(['status' => 3, 'remark' => $params['remark']]);
                $service = new AgencyOrderService();
                $service->plus($info->user_id, $info->money, '', '提现失败退回');
            });
            $this->success('审核成功');
        }
        $this->view->assign('row', $info);
        return $this->view->fetch();
    }
}
