<?php

namespace app\admin\controller\msjt\users;

use app\admin\controller\auth\Rule;
use app\common\controller\Backend;
use think\Db;

/**
 * 分销申请管理
 *
 * @icon fa fa-circle-o
 */
class Apply extends Backend
{

    /**
     * Apply模型对象
     * @var \app\admin\model\msjt\users\Apply
     */
    protected $model = null;
    protected $user = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\msjt\users\Apply;
        $this->user = new \app\admin\model\msjt\users\Users();
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

            $list = $this->model->with('users')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 申请通过
     * @param $ids
     */
    public function setSuccess($ids)
    {
        $status = Db::transaction(function () use ($ids) {
            $model = $this->model->get($ids);
            if ($model->status == 1) {
                $model->status = 2;
                $model->save();
                $this->user->where('id', $model['user_id'])->update(['dai' => 2, 'mobile' => $model['mobile']]);
                return true;
            }
        });
        if ($status) {
            $this->success('通过成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 拒绝
     * @param string $ids
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function review($ids = '')
    {
        if ($this->request->isPost()) {
            $status = false;
            $model = $this->model->get($ids);
            $params = $this->request->param();
            if ($model->status == 1) {
                $model->status = 3;
                $model->reason = $params['reason'];
                $status = $model->save();
            }
            if ($status) {
                $this->success('拒绝成功');
            } else {
                $this->error('操作失败');
            }
        }
        return $this->view->fetch();
    }
}
